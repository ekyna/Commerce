<?php

namespace Ekyna\Component\Commerce\Bridge\Symfony\EventListener;

use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Order\Event\OrderShipmentEvents;
use Ekyna\Component\Commerce\Order\Model\OrderShipmentInterface;
use Ekyna\Component\Commerce\Shipment\EventListener\AbstractShipmentListener;
use Ekyna\Component\Resource\Event\ResourceEventInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class OrderShipmentEventSubscriber
 * @package Ekyna\Bundle\CommerceBundle\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class OrderShipmentEventSubscriber extends AbstractShipmentListener implements EventSubscriberInterface
{
    /**
     * @inheritdoc
     */
    protected function getShipmentFromEvent(ResourceEventInterface $event)
    {
        $resource = $event->getResource();

        if (!$resource instanceof OrderShipmentInterface) {
            throw new InvalidArgumentException("Expected instance of OrderShipmentInterface");
        }

        return $resource;
    }

    /**
     * {@inheritDoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            OrderShipmentEvents::INSERT     => ['onInsert', 0],
            OrderShipmentEvents::UPDATE     => ['onUpdate', 0],
            OrderShipmentEvents::DELETE     => ['onDelete', 0],
            OrderShipmentEvents::PRE_DELETE => ['onPreDelete', 0],
        ];
    }
}
