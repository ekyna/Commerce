<?php

namespace Ekyna\Component\Commerce\Order\EventListener;

use Ekyna\Component\Commerce\Exception;
use Ekyna\Component\Commerce\Order\Event\OrderShipmentEvents;
use Ekyna\Component\Commerce\Order\Model\OrderShipmentItemInterface;
use Ekyna\Component\Commerce\Shipment\EventListener\AbstractShipmentItemListener;
use Ekyna\Component\Commerce\Shipment\Model as Shipment;
use Ekyna\Component\Resource\Event\ResourceEventInterface;

/**
 * Class OrderShipmentItemListener
 * @package Ekyna\Component\Commerce\Order\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class OrderShipmentItemListener extends AbstractShipmentItemListener
{
    /**
     * @inheritDoc
     */
    protected function preventSaleItemChange(Shipment\ShipmentItemInterface $item)
    {
        if (!$item instanceof OrderShipmentItemInterface) {
            throw new Exception\InvalidArgumentException("Expected instance of OrderShipmentItemInterface");
        }

        if ($this->persistenceHelper->isChanged($item, 'orderItem')) {
            list($old, $new) = $this->persistenceHelper->getChangeSet($item, 'orderItem');
            if ($old != $new) {
                throw new Exception\RuntimeException("Changing the shipment item's sale item is not yet supported.");
            }
        }
    }

    /**
     * @inheritDoc
     */
    protected function scheduleShipmentContentChangeEvent(Shipment\ShipmentInterface $shipment)
    {
        $this->persistenceHelper->scheduleEvent($shipment, OrderShipmentEvents::CONTENT_CHANGE);
    }

    /**
     * @inheritDoc
     */
    protected function getShipmentItemFromEvent(ResourceEventInterface $event)
    {
        $resource = $event->getResource();

        if (!$resource instanceof OrderShipmentItemInterface) {
            throw new Exception\InvalidArgumentException("Expected instance of OrderShipmentItemInterface");
        }

        return $resource;
    }
}
