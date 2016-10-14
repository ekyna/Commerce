<?php

namespace Ekyna\Component\Commerce\Bridge\Symfony\EventListener;

use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Order\Event\OrderShipmentEvents;
use Ekyna\Component\Commerce\Order\Event\OrderShipmentItemEvents;
use Ekyna\Component\Commerce\Order\Model\OrderShipmentItemInterface;
use Ekyna\Component\Commerce\Shipment\EventListener\AbstractShipmentItemListener;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentInterface;
use Ekyna\Component\Resource\Event\ResourceEventInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class OrderShipmentEventSubscriber
 * @package Ekyna\Bundle\CommerceBundle\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class OrderShipmentItemEventSubscriber extends AbstractShipmentItemListener implements EventSubscriberInterface
{
    /**
     * @inheritdoc
     */
    protected function dispatchShipmentContentChangeEvent(ShipmentInterface $shipment)
    {
        $event = $this->dispatcher->createResourceEvent($shipment);

        $this->dispatcher->dispatch(OrderShipmentEvents::CONTENT_CHANGE, $event);
    }

    /**
     * @inheritdoc
     */
    protected function getShipmentItemFromEvent(ResourceEventInterface $event)
    {
        $resource = $event->getResource();

        if (!$resource instanceof OrderShipmentItemInterface) {
            throw new InvalidArgumentException("Expected instance of OrderShipmentItemInterface");
        }

        return $resource;
    }

    /**
     * {@inheritDoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            OrderShipmentItemEvents::INSERT     => ['onInsert', 0],
            OrderShipmentItemEvents::UPDATE     => ['onUpdate', 0],
            OrderShipmentItemEvents::DELETE     => ['onDelete', 0],
            OrderShipmentItemEvents::PRE_DELETE => ['onPreDelete', 0],
        ];
    }
}
