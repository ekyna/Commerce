<?php

namespace Ekyna\Component\Commerce\Order\EventListener;

use Ekyna\Component\Commerce\Exception;
use Ekyna\Component\Commerce\Order\Event\OrderCreditEvents;
use Ekyna\Component\Commerce\Order\Model\OrderCreditItemInterface;
use Ekyna\Component\Commerce\Credit\EventListener\AbstractCreditItemListener;
use Ekyna\Component\Commerce\Credit\Model as Credit;
use Ekyna\Component\Resource\Event\ResourceEventInterface;

/**
 * Class OrderCreditItemListener
 * @package Ekyna\Component\Commerce\Order\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class OrderCreditItemListener extends AbstractCreditItemListener
{
    /**
     * @inheritDoc
     */
    protected function preventSaleItemOrShipmentItemChange(Credit\CreditItemInterface $item)
    {
        if (!$item instanceof OrderCreditItemInterface) {
            throw new Exception\InvalidArgumentException("Expected instance of OrderCreditItemInterface");
        }

        if ($this->persistenceHelper->isChanged($item, 'orderItem')) {
            list($old, $new) = $this->persistenceHelper->getChangeSet($item, 'orderItem');
            if ($old != $new) {
                throw new Exception\RuntimeException("Changing the credit item's sale item is not yet supported.");
            }
        }

        if ($this->persistenceHelper->isChanged($item, 'shipmentItem')) {
            list($old, $new) = $this->persistenceHelper->getChangeSet($item, 'shipmentItem');
            if ($old != $new) {
                throw new Exception\RuntimeException("Changing the credit item's shipment item is not yet supported.");
            }
        }
    }

    /**
     * @inheritdoc
     */
    protected function scheduleCreditContentChangeEvent(Credit\CreditInterface $credit)
    {
        $this->persistenceHelper->scheduleEvent(OrderCreditEvents::CONTENT_CHANGE, $credit);
    }

    /**
     * @inheritdoc
     */
    protected function getCreditItemFromEvent(ResourceEventInterface $event)
    {
        $resource = $event->getResource();

        if (!$resource instanceof OrderCreditItemInterface) {
            throw new Exception\InvalidArgumentException("Expected instance of OrderCreditItemInterface");
        }

        return $resource;
    }
}
