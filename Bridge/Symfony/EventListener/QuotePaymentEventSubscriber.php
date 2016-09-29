<?php

namespace Ekyna\Component\Commerce\Bridge\Symfony\EventListener;

use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Quote\Event\QuoteEvents;
use Ekyna\Component\Commerce\Quote\Event\QuotePaymentEvents;
use Ekyna\Component\Commerce\Payment\EventListener\AbstractPaymentListener;
use Ekyna\Component\Commerce\Quote\Model\QuotePaymentInterface;
use Ekyna\Component\Resource\Event\ResourceEventInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class QuotePaymentEventSubscriber
 * @package Ekyna\Bundle\CommerceBundle\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class QuotePaymentEventSubscriber extends AbstractPaymentListener implements EventSubscriberInterface
{
    /**
     * @inheritdoc
     */
    protected function dispatchSaleContentChangeEvent(SaleInterface $sale)
    {
        $event = $this->dispatcher->createResourceEvent($sale);

        $this->dispatcher->dispatch(QuoteEvents::CONTENT_CHANGE, $event);
    }

    /**
     * @inheritdoc
     */
    protected function getPaymentFromEvent(ResourceEventInterface $event)
    {
        $resource = $event->getResource();

        if (!$resource instanceof QuotePaymentInterface) {
            throw new InvalidArgumentException("Expected instance of QuotePaymentInterface");
        }

        return $resource;
    }

    /**
     * {@inheritDoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            QuotePaymentEvents::INSERT     => ['onInsert', 0],
            QuotePaymentEvents::UPDATE     => ['onUpdate', 0],
            QuotePaymentEvents::DELETE     => ['onDelete', 0],
            QuotePaymentEvents::PRE_DELETE => ['onPreDelete', 0],
        ];
    }
}
