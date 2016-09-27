<?php

namespace Ekyna\Component\Commerce\Bridge\Symfony\EventListener;

use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Order\Event\OrderPaymentEvents;
use Ekyna\Component\Commerce\Order\Model\OrderPaymentInterface;
use Ekyna\Component\Commerce\Payment\EventListener\AbstractPaymentListener;
use Ekyna\Component\Resource\Event\ResourceEventInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class OrderPaymentEventSubscriber
 * @package Ekyna\Bundle\CommerceBundle\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class OrderPaymentEventSubscriber extends AbstractPaymentListener implements EventSubscriberInterface
{
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

    /**
     * {@inheritDoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            OrderPaymentEvents::INSERT     => ['onInsert', 0],
            OrderPaymentEvents::UPDATE     => ['onUpdate', 0],
            OrderPaymentEvents::DELETE     => ['onDelete', 0],
            OrderPaymentEvents::PRE_DELETE => ['onPreDelete', 0],
        ];
    }
}
