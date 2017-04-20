<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Supplier\EventListener;

use Decimal\Decimal;
use Ekyna\Component\Commerce\Exception\RuntimeException;
use Ekyna\Component\Commerce\Exception\UnexpectedTypeException;
use Ekyna\Component\Commerce\Supplier\Model\SupplierDeliveryItemInterface;
use Ekyna\Component\Resource\Event\ResourceEventInterface;

/**
 * Class SupplierDeliveryItemListener
 * @package Ekyna\Component\Commerce\Supplier\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SupplierDeliveryItemListener extends AbstractListener
{
    // TODO assert that order is at least at ordered state (in delivery listener too ...)

    public function onInsert(ResourceEventInterface $event): void
    {
        $item = $this->getSupplierDeliveryItemFromEvent($event);

        // Credit stock unit received quantity
        if (null === $orderItem = $item->getOrderItem()) {
            throw new RuntimeException('OrderItem must be set.');
        }

        if (null !== $stockUnit = $orderItem->getStockUnit()) {
            $stockUnit->addGeocode($item->getGeocode());

            $this->stockUnitUpdater->updateReceived($stockUnit, $item->getQuantity(), true);
        } elseif ($orderItem->hasSubjectIdentity()) {
            throw new RuntimeException('Failed to retrieve stock unit.');
        }

        // Dispatch supplier order content change event
        if (null === $order = $orderItem->getOrder()) {
            throw new RuntimeException('Order must be set.');
        }

        $this->scheduleSupplierOrderContentChangeEvent($order);
    }

    public function onUpdate(ResourceEventInterface $event): void
    {
        $item = $this->getSupplierDeliveryItemFromEvent($event);

        if (null === $orderItem = $item->getOrderItem()) {
            throw new RuntimeException('OrderItem must be set.');
        }

        if ($this->persistenceHelper->isChanged($item, 'geocode')) {
            if (null !== $stockUnit = $orderItem->getStockUnit()) {
                $gCs = $this->persistenceHelper->getChangeSet($item, 'geocode');

                $stockUnit->removeGeocode($gCs[0]);
                $stockUnit->addGeocode($gCs[1]);

                $this->persistenceHelper->persistAndRecompute($stockUnit, false);
            } elseif ($orderItem->hasSubjectIdentity()) {
                throw new RuntimeException('Failed to retrieve stock unit.');
            }
        }

        if ($this->persistenceHelper->isChanged($item, 'quantity')) {
            $this->handleQuantityChange($item);

            // Dispatch supplier order content change event
            if (null === $order = $orderItem->getOrder()) {
                throw new RuntimeException('Order must be set.');
            }
            $this->scheduleSupplierOrderContentChangeEvent($order);

            // Remove item with zero quantity without event schedule
            if (0 == $item->getQuantity()) {
                $this->persistenceHelper->remove($item, false);
            }
        }
    }

    public function onDelete(ResourceEventInterface $event): void
    {
        $item = $this->getSupplierDeliveryItemFromEvent($event);

        $this->assertDeletable($item);

        if (null === $orderItem = $item->getOrderItem()) {
            throw new RuntimeException('OrderItem must be set.');
        }

        if (null !== $stockUnit = $orderItem->getStockUnit()) {
            $stockUnit->removeGeocode($item->getGeocode());
        } elseif ($orderItem->hasSubjectIdentity()) {
            throw new RuntimeException('Failed to retrieve stock unit.');
        }

        if ($this->persistenceHelper->isChanged($item, ['quantity'])) {
            $this->handleQuantityChange($item);
        } else {
            if (null !== $stockUnit) {
                // Debit stock unit received quantity
                $this->stockUnitUpdater->updateReceived($stockUnit, $item->getQuantity()->negate(), true);
            }

            // Trigger the supplier order update
            // TODO get from change set
            if (null === $order = $orderItem->getOrder()) {
                throw new RuntimeException('Failed to retrieve supplier order.');
            }

            if (!$this->persistenceHelper->isScheduledForRemove($order)) {
                $this->scheduleSupplierOrderContentChangeEvent($order);
            }
        }

        // Clear association
        $item->setDelivery(null);
    }

    /**
     * Handle the quantity change.
     */
    protected function handleQuantityChange(SupplierDeliveryItemInterface $item): void
    {
        $changeSet = $this->persistenceHelper->getChangeSet($item);

        // Delta quantity (difference between new and old)
        if (null === $orderItem = $item->getOrderItem()) {
            throw new RuntimeException('Failed to retrieve order item.');
        }
        if (null !== $stockUnit = $orderItem->getStockUnit()) {
            // TODO use packaging format
            $deltaQuantity = ($changeSet['quantity'][1] ?? new Decimal(0))
                ->sub($changeSet['quantity'][0] ?? new Decimal(0));
            if (!$deltaQuantity->isZero()) {
                // Update stock unit received quantity
                $this->stockUnitUpdater->updateReceived($stockUnit, $deltaQuantity, true);
            }
        } elseif ($orderItem->hasSubjectIdentity()) {
            throw new RuntimeException('Failed to retrieve stock unit.');
        }

        // Trigger the supplier order update
        if (null === $order = $orderItem->getOrder()) {
            throw new RuntimeException('Failed to retrieve order.');
        }
        $this->scheduleSupplierOrderContentChangeEvent($order);
    }

    public function onPreDelete(ResourceEventInterface $event): void
    {
        $item = $this->getSupplierDeliveryItemFromEvent($event);

        $this->assertDeletable($item);

        // Initialize the supplier deliveries items collection before the item removal.
        if (null !== $delivery = $item->getDelivery()) {
            $delivery->getItems();
        }
    }

    /**
     * Returns the supplier delivery item from the event.
     *
     * @throws UnexpectedTypeException
     */
    protected function getSupplierDeliveryItemFromEvent(ResourceEventInterface $event): SupplierDeliveryItemInterface
    {
        $item = $event->getResource();

        if (!$item instanceof SupplierDeliveryItemInterface) {
            throw new UnexpectedTypeException($item, SupplierDeliveryItemInterface::class);
        }

        return $item;
    }
}
