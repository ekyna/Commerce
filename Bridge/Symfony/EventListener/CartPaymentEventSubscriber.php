<?php

namespace Ekyna\Component\Commerce\Bridge\Symfony\EventListener;

use Ekyna\Component\Commerce\Cart\Event\CartEvents;
use Ekyna\Component\Commerce\Cart\Event\CartPaymentEvents;
use Ekyna\Component\Commerce\Cart\Model\CartPaymentInterface;
use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Payment\EventListener\AbstractPaymentListener;
use Ekyna\Component\Resource\Event\ResourceEventInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class CartPaymentEventSubscriber
 * @package Ekyna\Bundle\CommerceBundle\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CartPaymentEventSubscriber extends AbstractPaymentListener implements EventSubscriberInterface
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

    /**
     * {@inheritDoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            CartPaymentEvents::INSERT     => ['onInsert', 0],
            CartPaymentEvents::UPDATE     => ['onUpdate', 0],
            CartPaymentEvents::DELETE     => ['onDelete', 0],
            CartPaymentEvents::PRE_DELETE => ['onPreDelete', 0],
        ];
    }
}
