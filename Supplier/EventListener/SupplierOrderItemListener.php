<?php

namespace Ekyna\Component\Commerce\Supplier\EventListener;

use Ekyna\Component\Commerce\Exception\IllegalOperationException;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Supplier\Event\SupplierOrderEvents;
use Ekyna\Component\Commerce\Supplier\Model\SupplierOrderInterface;
use Ekyna\Component\Commerce\Supplier\Model\SupplierOrderItemInterface;
use Ekyna\Component\Resource\Event\ResourceEventInterface;

/**
 * Class SupplierOrderItemListener
 * @package Ekyna\Component\Commerce\Supplier\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SupplierOrderItemListener extends AbstractListener
{
    /**
     * Insert event handler.
     *
     * @param ResourceEventInterface $event
     */
    public function onInsert(ResourceEventInterface $event)
    {
        $item = $this->getSupplierOrderItemFromEvent($event);

        $changed = $this->syncSubjectDataWithProduct($item);

        if ($changed) {
            $this->persistenceHelper->persistAndRecompute($item);
        }

        // Create the stock unit
        $this->findStockUnit($item);

        $this->scheduleSupplierOrderContentChangeEvent($item->getOrder());
    }

    /**
     * Update event handler.
     *
     * @param ResourceEventInterface $event
     * @throws IllegalOperationException
     */
    public function onUpdate(ResourceEventInterface $event)
    {
        $item = $this->getSupplierOrderItemFromEvent($event);

        // Disallow product change
        if ($this->persistenceHelper->isChanged($item, 'product')) {
            $cs = $this->persistenceHelper->getChangeSet($item);
            if ($cs['product'][1] != $cs['product'][0]) {
                throw new IllegalOperationException("Changing supplier order item product is not supported yet.");
            }
        }

        $changed = $this->syncSubjectDataWithProduct($item);

        if ($changed) {
            $this->persistenceHelper->persistAndRecompute($item);
        }

        if ($this->persistenceHelper->isChanged($item, 'quantity')) {
            $this->updateOrderedQuantity($item);

            // TODO Prevent quantity to be set as lower than delivered quantity

            // Dispatch supplier order content change event
            $this->scheduleSupplierOrderContentChangeEvent($item->getOrder());
        }
    }

    /**
     * Delete event handler.
     *
     * @param ResourceEventInterface $event
     * @throws IllegalOperationException
     */
    public function onDelete(ResourceEventInterface $event)
    {
        $item = $this->getSupplierOrderItemFromEvent($event);

        // Prevent removal if related stock unit shipped quantity is greater than zero
        if ($this->isStockUnitShipped($item)) {
            throw new IllegalOperationException();
        }

        // Stock unit is configured for cascade removal at DBMS level:
        // ORM won't dispatch the delete event during flush.
        // Let's do it ourselves.
        $this->scheduleStockUnitDeleteEvent($item);

        // Supplier order has been set to null by the removeItem method.
        // Retrieve it from the change set.
        if (null === $order = $item->getOrder()) {
            $changeSet = $this->persistenceHelper->getChangeSet($item);
            if (array_key_exists('order', $changeSet)) {
                $order = $changeSet['order'][0];
            }
        }
        $this->scheduleSupplierOrderContentChangeEvent($order);
    }

    /**
     * Synchronises the subject data with the supplier product.
     *
     * @param SupplierOrderItemInterface $item
     *
     * @return bool Whether or not the item has been changed.
     */
    protected function syncSubjectDataWithProduct(SupplierOrderItemInterface $item)
    {
        if (null !== $product = $item->getProduct()) {
            if ($item->getSubjectData() != $product->getSubjectData()) {
                $item->setSubjectData($product->getSubjectData());

                return true;
            }
        } elseif (!empty($item->getSubjectData())) {
            $item->setSubjectData([]);

            return true;
        }

        return false;
    }

    /**
     * Returns the supplier order item from the event.
     *
     * @param ResourceEventInterface $event
     *
     * @return SupplierOrderItemInterface
     * @throws InvalidArgumentException
     */
    protected function getSupplierOrderItemFromEvent(ResourceEventInterface $event)
    {
        $item = $event->getResource();

        if (!$item instanceof SupplierOrderItemInterface) {
            throw new InvalidArgumentException("Expected instance of SupplierOrderItemInterface.");
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
