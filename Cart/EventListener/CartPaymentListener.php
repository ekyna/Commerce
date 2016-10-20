<?php

namespace Ekyna\Component\Commerce\Cart\EventListener;

use Ekyna\Component\Commerce\Cart\Event\CartEvents;
use Ekyna\Component\Commerce\Cart\Model\CartPaymentInterface;
use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Payment\EventListener\AbstractPaymentListener;
use Ekyna\Component\Resource\Event\ResourceEventInterface;

/**
 * Class CartPaymentListener
 * @package Ekyna\Component\Commerce\Cart\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CartPaymentListener extends AbstractPaymentListener
{
    /**
     * @inheritdoc
     */
    protected function dispatchSaleContentChangeEvent(SaleInterface $sale)
    {
        $event = $this->dispatcher->createResourceEvent($sale);

        $this->dispatcher->dispatch(CartEvents::CONTENT_CHANGE, $event);
    }

    /**
     * @inheritdoc
     */
    protected function getPaymentFromEvent(ResourceEventInterface $event)
    {
        $resource = $event->getResource();

        if (!$resource instanceof CartPaymentInterface) {
            throw new InvalidArgumentException("Expected instance of CartPaymentInterface");
        }

        return $resource;
    }
}
