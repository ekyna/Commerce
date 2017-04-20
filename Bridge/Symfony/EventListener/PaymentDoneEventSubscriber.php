<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Bridge\Symfony\EventListener;

use Ekyna\Component\Commerce\Payment\Event\PaymentEvents;
use Ekyna\Component\Commerce\Payment\EventListener\PaymentDoneEventSubscriber as BaseSubscriber;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class PaymentDoneEventSubscriber
 * @package Ekyna\Component\Commerce\Bridge\Symfony\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class PaymentDoneEventSubscriber extends BaseSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            PaymentEvents::STATUS => ['onStatus'],
        ];
    }
}
