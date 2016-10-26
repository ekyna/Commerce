<?php

namespace Ekyna\Component\Commerce\Supplier\EventListener;

use Ekyna\Component\Commerce\Exception\IllegalOperationException;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Exception\RuntimeException;
use Ekyna\Component\Commerce\Supplier\Event\SupplierOrderEvents;
use Ekyna\Component\Commerce\Supplier\Model\SupplierDeliveryItemInterface;
use Ekyna\Component\Commerce\Supplier\Model\SupplierOrderInterface;
use Ekyna\Component\Resource\Event\ResourceEventInterface;

/**
 * Class SupplierDeliveryItemListener
 * @package Ekyna\Component\Commerce\Supplier\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SupplierDeliveryItemListener extends AbstractListener
{
    /**
     * Insert event handler.
     *
     * @param ResourceEventInterface $event
     */
    public function onInsert(ResourceEventInterface $event)
    {
        $item = $this->getSupplierDeliveryItemFromEvent($event);
        if (null === $orderItem = $item->getOrderItem()) {
            throw new RuntimeException("OrderItem must be set.");
        }

        // Credit stock unit delivered quantity
        $this->updateDeliveredQuantity($orderItem, $item->getQuantity());

        // Dispatch supplier order content change event
        $this->scheduleSupplierOrderContentChangeEvent($orderItem->getOrder());
    }

    /**
     * Update event handler.
     *
     * @param ResourceEventInterface $event
     */
    public function onUpdate(ResourceEventInterface $event)
    {
        $item = $this->getSupplierDeliveryItemFromEvent($event);
        if (null === $orderItem = $item->getOrderItem()) {
            throw new RuntimeException("OrderItem must be set.");
        }

        if ($this->persistenceHelper->isChanged($item, ['quantity'])) {
            $changeSet = $this->persistenceHelper->getChangeSet($item);

            // Delta quantity (difference between new and old)
            if (0 != $deltaQuantity = floatval($changeSet['quantity'][1]) - floatval($changeSet['quantity'][0])) {
                // Update stock unit delivered quantity
                $this->updateDeliveredQuantity($orderItem, $deltaQuantity);
            }

            // Dispatch supplier order content change event
            $this->scheduleSupplierOrderContentChangeEvent($orderItem->getOrder());
        }
    }

    /**
     * Delete event handler.
     *
     * @param ResourceEventInterface $event
     */
    public function onDelete(ResourceEventInterface $event)
    {
        $item = $this->getSupplierDeliveryItemFromEvent($event);
        if (null === $orderItem = $item->getOrderItem()) {
            throw new RuntimeException("OrderItem must be set.");
        }

        // Debit stock unit delivered quantity
        $this->updateDeliveredQuantity($orderItem, -$item->getQuantity());

        // Trigger the supplier order item update
        $this->scheduleSupplierOrderContentChangeEvent($orderItem->getOrder());
    }

    /**
     * Pre delete event handler.
     *
     * @param ResourceEventInterface $event
     *
     * @throws IllegalOperationException
     */
    public function onPreDelete(ResourceEventInterface $event)
    {
        $item = $this->getSupplierDeliveryItemFromEvent($event);

        // Prevent removal if related stock unit shipped quantity is greater than zero
        if ($this->isStockUnitShipped($item->getOrderItem())) {
            throw new IllegalOperationException();
        }
    }

    /**
     * Returns the supplier delivery item from the event.
     *
     * @param ResourceEventInterface $event
     *
     * @return SupplierDeliveryItemInterface
     * @throws InvalidArgumentException
     */
    protected function getSupplierDeliveryItemFromEvent(ResourceEventInterface $event)
    {
        $item = $event->getResource();

        if (!$item instanceof SupplierDeliveryItemInterface) {
            throw new InvalidArgumentException("Expected instance of SupplierDeliveryItemInterface.");
        }

        return $item;
    }

    /**
     * Schedules the supplier order content change event.
     *
     * @param SupplierOrderInterface $order
     */
    protected function scheduleSupplierOrderContentChangeEvent(SupplierOrderInterface $order)
    {
        $this->persistenceHelper->scheduleEvent(SupplierOrderEvents::CONTENT_CHANGE, $order);
    }
}
