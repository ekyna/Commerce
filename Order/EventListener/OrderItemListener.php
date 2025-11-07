<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Order\EventListener;

use Ekyna\Component\Commerce\Common\EventListener\AbstractSaleItemListener;
use Ekyna\Component\Commerce\Common\Model;
use Ekyna\Component\Commerce\Exception\UnexpectedTypeException;
use Ekyna\Component\Commerce\Order\Event\OrderEvents;
use Ekyna\Component\Commerce\Order\Model\OrderItemInterface;
use Ekyna\Component\Commerce\Order\Model\OrderStates;
use Ekyna\Component\Commerce\Stock\Assigner\StockUnitAssignerInterface;
use Ekyna\Component\Resource\Event\ResourceEventInterface;

/**
 * Class OrderItemListener
 * @package Ekyna\Component\Commerce\Order\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class OrderItemListener extends AbstractSaleItemListener
{
    private StockUnitAssignerInterface $stockAssigner;

    public function setStockAssigner(StockUnitAssignerInterface $stockAssigner): void
    {
        $this->stockAssigner = $stockAssigner;
    }

    public function onInsert(ResourceEventInterface $event): void
    {
        parent::onInsert($event);

        $item = $this->getSaleItemFromEvent($event);

        // If order is in stockable state
        if (OrderStates::isStockableState($item->getRootSale())) {
            $this->stockAssigner->assignOrderItem($item);

            return;
        }

        $this->stockAssigner->detachOrderItem($item);
    }

    public function onUpdate(ResourceEventInterface $event): void
    {
        parent::onUpdate($event);

        $item = $this->getSaleItemFromEvent($event);

        if (!$this->persistenceHelper->isChanged($item, [
            'quantity',
            'subjectIdentity.provider',
            'subjectIdentity.identifier',
        ])) {
            return;
        }

        $sale = $item->getRootSale();

        // If sale state has changed
        if ($this->persistenceHelper->isChanged($sale, 'state')) {
            $stateCs = $this->persistenceHelper->getChangeSet($sale, 'state');

            $withRefunded = $sale->hasShipmentOrInvoice();

            // If order just did a stockable state transition
            if (
                OrderStates::hasChangedToStockable($stateCs, $withRefunded)
                || OrderStates::hasChangedFromStockable($stateCs, $withRefunded)
            ) {
                // Prevent assignments update (done by the order listener)
                return;
            }
        }

        // If sale released flag has changed
        if ($sale->isSample() && $this->persistenceHelper->isChanged($sale, 'released')) {
            // Prevent assignments update (done by the order listener)
            return;
        }

        // If order is in stockable state and order item quantity has changed
        if (OrderStates::isStockableState($sale)) {
            $this->applySaleItemRecursively($item);
        }
    }

    public function onDelete(ResourceEventInterface $event): void
    {
        parent::onDelete($event);

        $item = $this->getSaleItemFromEvent($event);

        if ($item->hasStockAssignments()) {
            $this->stockAssigner->detachOrderItem($item);
        }
    }

    /**
     * Applies the sale item to stock units recursively.
     *
     * @param OrderItemInterface $item
     */
    protected function applySaleItemRecursively(Model\SaleItemInterface $item): void
    {
        // If subject has changed
        if ($this->persistenceHelper->isChanged($item, ['subjectIdentity.provider', 'subjectIdentity.identifier'])) {
            $this->stockAssigner->detachOrderItem($item);
            $this->stockAssigner->assignOrderItem($item);
        } else {
            $this->stockAssigner->applyOrderItem($item);
        }

        foreach ($item->getChildren() as $child) {
            if (
                $this->persistenceHelper->isScheduledForInsert($child)
                || (
                    $this->persistenceHelper->isScheduledForUpdate($child)
                    && $this->persistenceHelper->isChanged($child, [
                        'quantity',
                        'subjectIdentity.provider',
                        'subjectIdentity.identifier',
                    ])
                )
            ) {
                // Skip this item as the listener will be called on it.
                /** @see OrderItemListener::onUpdate() */
                continue;
            }

            $this->applySaleItemRecursively($child);
        }
    }

    /**
     * @inheritDoc
     */
    protected function getSalePropertyPath(): string
    {
        return 'order';
    }

    /**
     * @inheritDoc
     */
    protected function scheduleSaleContentChangeEvent(Model\SaleInterface $sale): void
    {
        $this->persistenceHelper->scheduleEvent($sale, OrderEvents::CONTENT_CHANGE);
    }

    /**
     * @inheritDoc
     *
     * @return OrderItemInterface
     */
    protected function getSaleItemFromEvent(ResourceEventInterface $event): Model\SaleItemInterface
    {
        $item = $event->getResource();

        if (!$item instanceof OrderItemInterface) {
            throw new UnexpectedTypeException($item, OrderItemInterface::class);
        }

        return $item;
    }
}
