<?php

namespace Ekyna\Component\Commerce\Order\EventListener;

use Ekyna\Component\Commerce\Common\Calculator\MarginCalculatorInterface;
use Ekyna\Component\Commerce\Common\EventListener\AbstractSaleListener;
use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Common\Model\SaleItemInterface;
use Ekyna\Component\Commerce\Exception\IllegalOperationException;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Order\Event\OrderEvents;
use Ekyna\Component\Commerce\Order\Model\OrderInterface;
use Ekyna\Component\Commerce\Order\Model\OrderStates;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentStates;
use Ekyna\Component\Commerce\Stock\Assigner\StockUnitAssignerInterface;
use Ekyna\Component\Resource\Event\ResourceEventInterface;

/**
 * Class OrderEventSubscriber
 * @package Ekyna\Component\Commerce\Order\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class OrderListener extends AbstractSaleListener
{
    /**
     * @var StockUnitAssignerInterface
     */
    protected $stockAssigner;

    /**
     * @var MarginCalculatorInterface
     */
    protected $marginCalculator;


    /**
     * Sets the stock assigner.
     *
     * @param StockUnitAssignerInterface $stockAssigner
     */
    public function setStockAssigner(StockUnitAssignerInterface $stockAssigner)
    {
        $this->stockAssigner = $stockAssigner;
    }

    /**
     * Sets the margin calculator.
     *
     * @param MarginCalculatorInterface $marginCalculator
     */
    public function setMarginCalculator($marginCalculator)
    {
        $this->marginCalculator = $marginCalculator;
    }

    /**
     * @inheritdoc
     */
    public function onPreDelete(ResourceEventInterface $event)
    {
        parent::onPreDelete($event);

        /** @var OrderInterface $order */
        $order = $this->getSaleFromEvent($event);

        // Stop if order has valid shipments
        if (null !== $shipments = $order->getShipments()) {
            /** @var \Ekyna\Component\Commerce\Shipment\Model\ShipmentInterface $shipment */
            foreach ($shipments as $shipment) {
                if (!ShipmentStates::isDeletableState($shipment->getState())) {
                    throw new IllegalOperationException(); // TODO Reason message
                }
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function onPreUpdate(ResourceEventInterface $event)
    {
        /** @var OrderInterface $sale */
        $sale = $this->getSaleFromEvent($event);

        if ($sale->isSample() && ($sale->hasPayments() || $sale->hasInvoices())) {
            throw new IllegalOperationException( // TODO Translation
                "Order with payments or invoices can't be turned into sample order."
            );
        }
    }

    /**
     * @inheritDoc
     *
     * @param OrderInterface $sale
     */
    public function handleStateChange(SaleInterface $sale)
    {
        parent::handleStateChange($sale);

        if ($this->persistenceHelper->isChanged($sale, 'state')) {
            $stateCs = $this->persistenceHelper->getChangeSet($sale, 'state');

            // If order state has changed from non stockable to stockable
            if (OrderStates::hasChangedToStockable($stateCs)) {
                foreach ($sale->getItems() as $item) {
                    $this->assignSaleItemRecursively($item);
                }
            }
            // If order state has changed from stockable to non stockable
            elseif (OrderStates::hasChangedFromStockable($stateCs)) {
                foreach ($sale->getItems() as $item) {
                    $this->detachSaleItemRecursively($item);
                }
                // We don't need to handle invoices as they are detached with sale items.
            }
        }
    }

    /**
     * @inheritDoc
     *
     * @param OrderInterface $sale
     */
    protected function handleContentChange(SaleInterface $sale)
    {
        $changed = parent::handleContentChange($sale);

        if (null !== $margin = $this->marginCalculator->calculateSale($sale)) {
            $amount = $margin->getAmount();
        } else {
            $amount = 0;
        }

        if ($sale->getMarginTotal() != $amount) {
            $sale->setMarginTotal($amount);
            $changed = true;
        }

        if ($sale->getItemsCount() != $count = $sale->getItems()->count()) {
            $sale->setItemsCount($count);
            $changed = true;
        }

        return $changed;
    }

    /**
     * @inheritDoc
     */
    protected function isDiscountUpdateNeeded(SaleInterface $sale)
    {
        if ($this->persistenceHelper->isChanged($sale, 'sample')) {
            return true;
        }

        return parent::isDiscountUpdateNeeded($sale);
    }

    /**
     * @inheritDoc
     */
    protected function isTaxationUpdateNeeded(SaleInterface $sale)
    {
        if ($this->persistenceHelper->isChanged($sale, 'sample')) {
            return true;
        }

        return parent::isTaxationUpdateNeeded($sale);
    }

    /**
     * @inheritDoc
     */
    protected function isShipmentTaxationUpdateNeeded(SaleInterface $sale)
    {
        if ($this->persistenceHelper->isChanged($sale, 'sample')) {
            return true;
        }

        return parent::isShipmentTaxationUpdateNeeded($sale);
    }

    /**
     * @inheritdoc
     *
     * @param OrderInterface $sale
     */
    protected function updateState(SaleInterface $sale)
    {
        if (parent::updateState($sale)) {
            if (in_array($sale->getState(), OrderStates::getStockableStates(), true)) {
                if (($sale->getState() === OrderStates::STATE_COMPLETED) && (null === $sale->getCompletedAt())) {
                    $sale->setCompletedAt(new \DateTime());
                } elseif (($sale->getState() !== OrderStates::STATE_COMPLETED) && (null !== $sale->getCompletedAt())) {
                    $sale->setCompletedAt(null);
                }

                if (null === $sale->getAcceptedAt()) {
                    $sale->setAcceptedAt(new \DateTime());
                }
            } else {
                $sale
                    ->setAcceptedAt(null)
                    ->setCompletedAt(null);
            }

            return true;
        }

        return false;
    }

    /**
     * Assigns the sale item to stock units recursively.
     *
     * @param SaleItemInterface $item
     */
    protected function assignSaleItemRecursively(SaleItemInterface $item)
    {
        $this->stockAssigner->assignSaleItem($item);

        foreach ($item->getChildren() as $child) {
            $this->assignSaleItemRecursively($child);
        }
    }

    /**
     * Detaches the sale item from stock units recursively.
     *
     * @param SaleItemInterface $item
     */
    protected function detachSaleItemRecursively(SaleItemInterface $item)
    {
        $this->stockAssigner->detachSaleItem($item);

        foreach ($item->getChildren() as $child) {
            $this->detachSaleItemRecursively($child);
        }
    }

    /**
     * @inheritdoc
     */
    protected function getSaleFromEvent(ResourceEventInterface $event)
    {
        $resource = $event->getResource();

        if (!$resource instanceof OrderInterface) {
            throw new InvalidArgumentException("Expected instance of OrderInterface");
        }

        return $resource;
    }

    /**
     * @inheritdoc
     */
    protected function scheduleContentChangeEvent(SaleInterface $sale)
    {
        if (!$sale instanceof OrderInterface) {
            throw new InvalidArgumentException("Expected instance of OrderInterface");
        }

        $this->persistenceHelper->scheduleEvent(OrderEvents::CONTENT_CHANGE, $sale);
    }

    /**
     * @inheritdoc
     */
    protected function scheduleStateChangeEvent(SaleInterface $sale)
    {
        if (!$sale instanceof OrderInterface) {
            throw new InvalidArgumentException("Expected instance of OrderInterface");
        }

        $this->persistenceHelper->scheduleEvent(OrderEvents::STATE_CHANGE, $sale);
    }
}
