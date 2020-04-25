<?php

namespace Ekyna\Component\Commerce\Supplier\EventListener;

use Ekyna\Component\Commerce\Exception;
use Ekyna\Component\Commerce\Supplier\Model\SupplierDeliveryInterface;
use Ekyna\Component\Resource\Event\ResourceEventInterface;

/**
 * Class SupplierDeliveryListener
 * @package Ekyna\Component\Commerce\Supplier\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SupplierDeliveryListener extends AbstractListener
{
    /**
     * Delete event handler.
     *
     * @param ResourceEventInterface $event
     */
    public function onDelete(ResourceEventInterface $event)
    {
        $delivery = $this->getSupplierDeliveryFromEvent($event);

        if (null === $order = $delivery->getOrder()) {
            $changeSet = $this->persistenceHelper->getChangeSet($delivery);
            if (array_key_exists('order', $changeSet)) {
                $order = $changeSet['order'][0];
            }
        }
        if (null === $order) {
            throw new Exception\RuntimeException("Failed to retrieve supplier order.");
        }

        // Clear association
        $delivery->setOrder(null);

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
        $delivery = $this->getSupplierDeliveryFromEvent($event);

        $this->assertDeletable($delivery);

        // Initialize the supplier order's deliveries collection before the delivery removal.
        // @see http://stackoverflow.com/questions/41102378/scheduled-entity-in-onflush-is-different-instance#answer-41361138
        $delivery->getOrder()->getDeliveries()->count();
    }

    /**
     * Returns the supplier delivery item from the event.
     *
     * @param ResourceEventInterface $event
     *
     * @return SupplierDeliveryInterface
     * @throws Exception\InvalidArgumentException
     */
    protected function getSupplierDeliveryFromEvent(ResourceEventInterface $event)
    {
        $delivery = $event->getResource();

        if (!$delivery instanceof SupplierDeliveryInterface) {
            throw new Exception\InvalidArgumentException("Expected instance of SupplierDeliveryInterface.");
        }

        return $delivery;
    }
}
