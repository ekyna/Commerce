<?php

namespace Ekyna\Component\Commerce\Order\EventListener;

use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Credit\Model\CreditInterface;
use Ekyna\Component\Commerce\Exception;
use Ekyna\Component\Commerce\Order\Event\OrderEvents;
use Ekyna\Component\Commerce\Order\Model\OrderCreditInterface;
use Ekyna\Component\Commerce\Credit\EventListener\AbstractCreditListener;
use Ekyna\Component\Resource\Event\ResourceEventInterface;

/**
 * Class OrderCreditListener
 * @package Ekyna\Component\Commerce\Order\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class OrderCreditListener extends AbstractCreditListener
{
    /**
     * @inheritDoc
     */
    protected function preventSaleOrShipmentChange(CreditInterface $credit)
    {
        if (!$credit instanceof OrderCreditInterface) {
            throw new Exception\InvalidArgumentException("Expected instance of OrderCreditInterface");
        }

        if ($this->persistenceHelper->isChanged($credit, 'order')) {
            list($old, $new) = $this->persistenceHelper->getChangeSet($credit, 'order');
            if ($old != $new) {
                throw new Exception\RuntimeException("Changing the credit's order is not yet supported.");
            }
        }

        if ($this->persistenceHelper->isChanged($credit, 'shipment')) {
            list($old, $new) = $this->persistenceHelper->getChangeSet($credit, 'shipment');
            if ($old != $new) {
                throw new Exception\RuntimeException("Changing the credit's shipment is not yet supported.");
            }
        }
    }

    /**
     * @inheritdoc
     */
    protected function scheduleSaleContentChangeEvent(SaleInterface $sale)
    {
        $this->persistenceHelper->scheduleEvent(OrderEvents::CONTENT_CHANGE, $sale);
    }

    /**
     * @inheritdoc
     */
    protected function getCreditFromEvent(ResourceEventInterface $event)
    {
        $resource = $event->getResource();

        if (!$resource instanceof OrderCreditInterface) {
            throw new Exception\InvalidArgumentException("Expected instance of OrderCreditInterface");
        }

        return $resource;
    }
}
