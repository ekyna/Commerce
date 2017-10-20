<?php

namespace Ekyna\Component\Commerce\Bridge\Symfony\EventListener;

use Ekyna\Component\Commerce\Pricing\EventListener\TaxGroupListener;
use Ekyna\Component\Commerce\Pricing\Event\TaxGroupEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class TaxGroupEventSubscriber
 * @package Ekyna\Component\Commerce\Bridge\Symfony\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class TaxGroupEventSubscriber extends TaxGroupListener implements EventSubscriberInterface
{
    /**
     * @inheritdoc
     */
    public static function getSubscribedEvents()
    {
        return [
            TaxGroupEvents::INSERT     => ['onInsert', 0],
            TaxGroupEvents::UPDATE     => ['onUpdate', 0],
            TaxGroupEvents::PRE_DELETE => ['onPreDelete', 0],
        ];
    }
}
