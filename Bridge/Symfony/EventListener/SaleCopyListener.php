<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Bridge\Symfony\EventListener;

use Ekyna\Component\Commerce\Common\Event\SaleTransformEvent;
use Ekyna\Component\Commerce\Order\Model\OrderInterface;
use Ekyna\Component\Commerce\Order\Model\OrderStates;
use Ekyna\Component\Resource\Event\ResourceMessage;

/**
 * Class SaleTransformSubscriber
 * @package Ekyna\Component\Commerce\Bridge\Symfony\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SaleCopyListener
{
    public function onInitTransform(SaleTransformEvent $event): void
    {
        $source = $event->getSource();

        if (!$source instanceof OrderInterface) {
            return;
        }

        if ($source->getState() === OrderStates::STATE_NEW) {
            return;
        }

        // Prevent if order is not 'new'
        $message = ResourceMessage::create(
            'sale.message.transform_prevented',
            ResourceMessage::TYPE_ERROR
        )->setDomain('EkynaCommerce');

        $event->addMessage($message);
    }

    public function onPostCopy(SaleTransformEvent $event): void
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

        // If target sale is order and source customer has a parent
        if (!$target instanceof OrderInterface) {
            return;
        }
        if (!$customer->hasParent()) {
            return;
        }

        // TODO Duplicate code
        /** @see \Ekyna\Component\Commerce\Order\EventListener\OrderListener::fixCustomers() */

        // Sets the parent as customer
        $target->setCustomer($customer->getParent());

        // Sets the origin customer
        if (null === $target->getOriginCustomer()) {
            $target->setOriginCustomer($customer);
        }
    }
}
