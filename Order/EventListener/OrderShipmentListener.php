<?php

namespace Ekyna\Component\Commerce\Order\EventListener;

use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Order\Event\OrderEvents;
use Ekyna\Component\Commerce\Order\Model\OrderShipmentInterface;
use Ekyna\Component\Commerce\Shipment\EventListener\AbstractShipmentListener;
use Ekyna\Component\Resource\Event\ResourceEventInterface;

/**
 * Class OrderShipmentListener
 * @package Ekyna\Component\Commerce\Order\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class OrderShipmentListener extends AbstractShipmentListener
{
    /**
     * @inheritDoc
     */
    protected function scheduleSaleContentChangeEvent(SaleInterface $sale)
    {
        $this->persistenceHelper->scheduleEvent($sale, OrderEvents::CONTENT_CHANGE);
    }

    /**
     * @inheritDoc
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
     * @inheritDoc
     */
    protected function getSalePropertyPath()
    {
        return 'order';
    }
}
