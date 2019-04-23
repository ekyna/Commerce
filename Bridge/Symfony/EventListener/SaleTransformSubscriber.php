<?php

namespace Ekyna\Component\Commerce\Bridge\Symfony\EventListener;

use Ekyna\Component\Commerce\Common\Event\SaleTransformEvent;
use Ekyna\Component\Commerce\Common\Event\SaleTransformEvents;
use Ekyna\Component\Commerce\Order\Model\OrderInterface;
use Ekyna\Component\Commerce\Order\Model\OrderStates;
use Ekyna\Component\Resource\Event\ResourceMessage;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class SaleTransformSubscriber
 * @package Ekyna\Component\Commerce\Bridge\Symfony\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SaleTransformSubscriber implements EventSubscriberInterface
{
    /**
     * Pre copy event handler.
     *
     * @param SaleTransformEvent $event
     */
    public function onPreCopy(SaleTransformEvent $event)
    {
        $source = $event->getSource();

        if ($source instanceof OrderInterface) {
            // Prevent if order is not 'new'
            if ($source->getState() !== OrderStates::STATE_NEW) {
                $event->addMessage(new ResourceMessage(
                    'ekyna_commerce.sale.message.transform_prevented',
                    ResourceMessage::TYPE_ERROR
                ));
            }
        }
    }

    /**
     * Post copy event handler.
     *
     * @param SaleTransformEvent $event
     */
    public function onPostCopy(SaleTransformEvent $event)
    {
        $source = $event->getSource();
        $target = $event->getTarget();

        // Origin number
        $target->setOriginNumber($source->getNumber());

        // Sample
        if ($source instanceof OrderInterface && $target instanceof OrderInterface) {
            $target->setSample($source->isSample());
        }

        // Abort if source sale has no customer
        if (null === $customer = $source->getCustomer()) {
            return;
        }

        // If target sale is order and source customer has parent
        if ($target instanceof OrderInterface && $customer->hasParent()) {
            // Sets the parent as customer
            $target->setCustomer($customer->getParent());

            // Sets the origin customer
            if (null === $target->getOriginCustomer()) {
                $target->setOriginCustomer($customer);
            }
        }
    }

    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents()
    {
        return [
            SaleTransformEvents::PRE_COPY  => ['onPreCopy', 2048],
            SaleTransformEvents::POST_COPY => ['onPostCopy', 2048],
        ];
    }
}
