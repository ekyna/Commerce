<?php

namespace Ekyna\Component\Commerce\Order\EventListener;

use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Order\Event\OrderEvents;
use Ekyna\Component\Commerce\Order\Model\OrderPaymentInterface;
use Ekyna\Component\Commerce\Payment\EventListener\AbstractPaymentListener;
use Ekyna\Component\Resource\Event\ResourceEventInterface;

/**
 * Class OrderPaymentListener
 * @package Ekyna\Component\Commerce\Order\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class OrderPaymentListener extends AbstractPaymentListener
{
    /**
     * @inheritdoc
     */
    public function onPreDelete(ResourceEventInterface $event)
    {
        if ($event->getHard()) {
            return;
        }

        parent::onPreDelete($event);
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
    protected function getPaymentFromEvent(ResourceEventInterface $event)
    {
        $resource = $event->getResource();

        if (!$resource instanceof OrderPaymentInterface) {
            throw new InvalidArgumentException("Expected instance of OrderPaymentInterface");
        }

        return $resource;
    }
}
