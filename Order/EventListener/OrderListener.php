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
use Ekyna\Component\Commerce\Order\Repository\OrderRepositoryInterface;
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
     * @var OrderRepositoryInterface
     */
    protected $orderRepository;


    /**
     * Sets the stock assigner.
     *
     * @param StockUnitAssignerInterface $assigner
     */
    public function setStockAssigner(StockUnitAssignerInterface $assigner)
    {
        $this->stockAssigner = $assigner;
    }

    /**
     * Sets the margin calculator.
     *
     * @param MarginCalculatorInterface $calculator
     */
    public function setMarginCalculator(MarginCalculatorInterface $calculator)
    {
        $this->marginCalculator = $calculator;
    }

    /**
     * Sets the order repository.
     *
     * @param OrderRepositoryInterface $repository
     */
    public function setOrderRepository(OrderRepositoryInterface $repository)
    {
        $this->orderRepository = $repository;
    }

    /**
     * Prepare event handler.
     *
     * @param ResourceEventInterface $event
     *
     * @throws IllegalOperationException
     */
    public function onPrepare(ResourceEventInterface $event)
    {
        $order = $this->getSaleFromEvent($event);

        if (!OrderStates::isStockableState($order->getState())) {
            throw new IllegalOperationException(
                "Order is not ready for shipment preparation"
            );
        }
    }

    /**
     * @inheritdoc
     */
    public function onPreDelete(ResourceEventInterface $event)
    {
        parent::onPreDelete($event);

        $order = $this->getSaleFromEvent($event);

        // Stop if order has invoices or shipments
        if ($order->hasInvoices() || $order->hasShipments()) {
            throw new IllegalOperationException(
                "Order with invoices or shipments can't be deleted."
            );
        }
    }

    /**
     * @inheritDoc
     */
    public function onPreUpdate(ResourceEventInterface $event)
    {
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
    protected function handleInsert(SaleInterface $sale)
    {
        $changed = $this->fixCustomers($sale);

        $changed |= $this->setIsFirst($sale);

        $changed |= parent::handleInsert($sale);

        $changed |= $this->updateExchangeRate($sale);

        $changed |= $this->updateLocale($sale);

        return $changed;
    }

    /**
     * @inheritDoc
     *
     * @param OrderInterface $sale
     */
    protected function handleUpdate(SaleInterface $sale)
    {
        $changed = $this->fixCustomers($sale);

        $changed |= parent::handleUpdate($sale);

        $changed |= $this->handleReleasedChange($sale);

        return $changed;
    }

    /**
     * Handles the released flag change.
     *
     * @param OrderInterface $order
     *
     * @return bool
     */
    public function handleReleasedChange(OrderInterface $order)
    {
        if ($this->persistenceHelper->isChanged($order , 'sample')) {
            if ($order->isReleased() && !$order->isSample()) {
                throw new IllegalOperationException("Can't turn 'sample' into false if order is released.");
            }
        }

        if (!$this->persistenceHelper->isChanged($order , 'released')) {
            return false;
        }

        // Orders that are not samples can't be released.
        if (!$order->isSample() && $order->isReleased()) {
            $order->setReleased(false);
            return true;
        }

        if (!OrderStates::isStockableState($order->getState())) {
            return false;
        }

        foreach ($order->getItems() as $item) {
            $this->applySaleItemRecursively($item);
        }

        return false;
    }

    /**
     * Sets whether this order is the customer's first one.
     *
     * @param OrderInterface $order
     *
     * @return bool Whether the order has been changed.
     */
    protected function setIsFirst(OrderInterface $order)
    {
        if (null !== $customer = $order->getCustomer()) {
            if ($customer->hasParent()) {
                $customer = $customer->getParent();
            }
            $first = !$this->orderRepository->existsForCustomer($customer);
        } else {
            $first = !$this->orderRepository->existsForEmail($order->getEmail());
        }

        if ($first != $order->isFirst()) {
            $order->setFirst($first);

            return true;
        }

        return false;
    }

    /**
     * Changes the customer and origin customer regarding to their hierarchy.
     *
     * @param OrderInterface $order
     *
     * @return bool
     */
    protected function fixCustomers(OrderInterface $order)
    {
        $changed = false;

        $originCustomer = $order->getOriginCustomer();
        $customer = $order->getCustomer();

        if (is_null($customer)) {
            if ($originCustomer && $originCustomer->hasParent()) {
                $order->setCustomer($originCustomer->getParent());

                $changed = true;
            }
        } elseif ($customer->hasParent()) {
            $order->setCustomer($customer->getParent());

            if (null === $order->getOriginCustomer()) {
                $order->setOriginCustomer($customer);
            }

            $changed = true;
        }

        if ($changed) {
            $this->persistenceHelper->persistAndRecompute($order, false);
        }

        return $changed;
    }

    /**
     * @inheritDoc
     *
     * @param OrderInterface $sale
     */
    protected function handleStateChange(SaleInterface $sale)
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
     * @inheritDoc
     *
     * @param OrderInterface $sale
     */
    protected function updateVatDisplayMode(SaleInterface $sale)
    {
        // Vat display mode must not change if order has shipments or invoices.
        if ($sale->hasShipments() || $sale->hasInvoices()) {
            return false;
        }

        return parent::updateVatDisplayMode($sale);
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
     * Applies the sale item to stock units recursively.
     *
     * @param SaleItemInterface $item
     */
    protected function applySaleItemRecursively(SaleItemInterface $item)
    {
        $this->stockAssigner->applySaleItem($item);

        foreach ($item->getChildren() as $child) {
            $this->applySaleItemRecursively($child);
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
     *
     * @return OrderInterface
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
