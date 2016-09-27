<?php

namespace Ekyna\Component\Commerce\Order\EventListener;

use Ekyna\Component\Commerce\Common\EventListener\AbstractSaleListener;
use Ekyna\Component\Commerce\Exception\IllegalOperationException;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Order\Model\OrderInterface;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentStates;
use Ekyna\Component\Resource\Event\PersistenceEvent;
use Ekyna\Component\Resource\Event\ResourceEventInterface;

/**
 * Class OrderEventSubscriber
 * @package Ekyna\Component\Commerce\Order\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class OrderListener extends AbstractSaleListener
{
    /**
     * @inheritdoc
     */
    public function onInsert(PersistenceEvent $event)
    {
        $sale = $this->getSaleFromEvent($event);

        // TODO shipments ...

        parent::onInsert($event);
    }

    /**
     * @inheritdoc
     */
    public function onUpdate(PersistenceEvent $event)
    {
        $sale = $this->getSaleFromEvent($event);

        // TODO shipments ...

        parent::onUpdate($event);
    }

    /**
     * @inheritdoc
     */
    public function onPreDelete(ResourceEventInterface $event)
    {
        parent::onPreDelete($event);

        /** @var OrderInterface $order */
        $order = $this->getSaleFromEvent($event);

        // Stop if order has valid shipments
        if (null !== $shipments = $order->getShipments()) {
            foreach ($shipments as $shipment) {
                if (!in_array($shipment->getState(), ShipmentStates::getDeletableStates())) {
                    throw new IllegalOperationException();
                }
            }
        }
    }

    /**
     * @inheritdoc
     */
    protected function getSaleFromEvent(ResourceEventInterface $event)
    {
        $resource = $event->getResource();

        if (!$resource instanceof OrderInterface) {
            throw new InvalidArgumentException("Expected instance of OrderInterface");
        }

        return $resource;
    }
}
