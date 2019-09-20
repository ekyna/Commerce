<?php

namespace Ekyna\Component\Commerce\Supplier\EventListener;

use Ekyna\Component\Commerce\Exception\IllegalOperationException;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Exception\LogicException;
use Ekyna\Component\Commerce\Exception\RuntimeException;
use Ekyna\Component\Commerce\Supplier\Model\SupplierOrderItemInterface;
use Ekyna\Component\Commerce\Supplier\Model\SupplierOrderStates;
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

        $changed = $this->synchronizeWithProduct($item);

        if ($changed) {
            $this->persistenceHelper->persistAndRecompute($item);

            $this->scheduleSupplierOrderContentChangeEvent($item->getOrder());
        }

        // If supplier order state is 'ordered', 'partial' or 'completed'
        if (SupplierOrderStates::isStockableState($item->getOrder()->getState())) {
            // Associated stock unit (if not exists) must be created (absolute ordered quantity).
            $this->stockUnitLinker->linkItem($item);
        } else { // Supplier order state is 'new' or 'canceled'
            // Associated stock unit (if exists) must be deleted.
            $this->stockUnitLinker->unlinkItem($item);
        }
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
            $productCs = $this->persistenceHelper->getChangeSet($item, 'product');
            if ($productCs[0] != $productCs[1]) {
                // TODO message as translation id
                throw new IllegalOperationException("Changing supplier order item product is not supported yet.");
            }
        }

        $changed = $this->synchronizeWithProduct($item);
        if ($changed) {
            $this->persistenceHelper->persistAndRecompute($item, false);
        }

        // TODO These tests are made in the supplier order listener and should not be done twice...
        $order = $item->getOrder();
        if ($this->persistenceHelper->isChanged($order, 'state')) {
            $stateCs = $this->persistenceHelper->getChangeSet($order, 'state');

            // If order just did a stockable state transition
            if (
                SupplierOrderStates::hasChangedFromStockable($stateCs) ||
                SupplierOrderStates::hasChangedToStockable($stateCs)
            ) {
                // Abort (handled by the supplier order listener)
                return;
            }
        }

        if (SupplierOrderStates::isStockableState($order->getState())) {
            // Updates the ordered quantity and price
            if ($this->stockUnitLinker->applyItem($item)) {
                // Dispatch supplier order content change event
                $this->scheduleSupplierOrderContentChangeEvent($item->getOrder());
            }
        }
    }

    /**
     * Delete event handler.
     *
     * @param ResourceEventInterface $event
     */
    public function onDelete(ResourceEventInterface $event)
    {
        $item = $this->getSupplierOrderItemFromEvent($event);

        $this->assertDeletable($item);

        // TODO If not made by the supplierOrderListener ?
        //$this->deleteSupplierOrderItemStockUnit($item);
        $this->stockUnitLinker->unlinkItem($item);

        // Supplier order has been set to null by the removeItem method.
        // Retrieve it from the change set.
        if (null === $order = $item->getOrder()) {
            $changeSet = $this->persistenceHelper->getChangeSet($item);
            if (array_key_exists('order', $changeSet)) {
                $order = $changeSet['order'][0];
            }
        }
        if (null === $order) {
            throw new RuntimeException("Failed to retrieve supplier order.");
        }

        // Clear association
        $item->setOrder(null);
        /* @see SupplierDeliveryListener::onDelete */
        //$order->getItems()->removeElement($item);

        // Trigger the supplier order update
        if (!$this->persistenceHelper->isScheduledForRemove($order)) {
            $this->scheduleSupplierOrderContentChangeEvent($order);
        }
    }

    /**
     * Pre delete event handler.
     *
     * @param ResourceEventInterface $event
     */
    public function onPreDelete(ResourceEventInterface $event)
    {
        $item = $this->getSupplierOrderItemFromEvent($event);

        $this->assertDeletable($item);

        // Initializes the supplier order's items collection before item removal.
        $item->getOrder()->getItems();
    }

    /**
     * Synchronises with the supplier product.
     *
     * @param SupplierOrderItemInterface $item
     *
     * @return bool Whether or not the item has been changed.
     *
     * @throws LogicException If breaking synchronization between supplier order item and supplier product.
     * @throws InvalidArgumentException If supplier product subject data is not set.
     */
    protected function synchronizeWithProduct(SupplierOrderItemInterface $item)
    {
        $changed = false;

        // TODO What about stock management if subject change ???
        if (null !== $product = $item->getProduct()) {
            // TODO Create an utility class to do this
            $productSID = $product->getSubjectIdentity();
            if ($productSID->hasIdentity()) {
                $itemSID = $item->getSubjectIdentity();

                if ($itemSID->hasIdentity()) {
                    if (!$itemSID->equals($productSID)) {
                        throw new LogicException(
                            'Breaking synchronization between supplier order item and supplier product is not supported.'
                        );
                    }
                    $changed = false;
                } else {
                    $itemSID->copy($productSID);
                    $changed = true;
                }
            } else {
                throw new InvalidArgumentException(
                    'Supplier product subject identity is not set.'
                );
            }

            if (empty($item->getDesignation())) {
                $item->setDesignation($product->getDesignation());
            }
            if (empty($item->getReference())) {
                $item->setReference($product->getReference());
            }
            if (is_null($item->getNetPrice())) {
                $item->setNetPrice($product->getNetPrice());
            }
        } elseif ($item->hasSubjectIdentity()) {
            throw new LogicException(
                'Breaking synchronization between supplier order item and supplier product is not supported.'
            );
        }

        return $changed;
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
}
