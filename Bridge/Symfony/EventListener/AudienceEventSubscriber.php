<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Bridge\Symfony\EventListener;

use Ekyna\Component\Commerce\Newsletter\Event\AudienceEvents;
use Ekyna\Component\Commerce\Newsletter\EventListener\AudienceListener;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class AudienceEventSubscriber
 * @package Ekyna\Component\Commerce\Bridge\Symfony\EventListener
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class AudienceEventSubscriber extends AudienceListener implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            AudienceEvents::PRE_CREATE => ['onPreCreate', 0],
            AudienceEvents::PRE_UPDATE => ['onPreUpdate', 0],
            AudienceEvents::PRE_DELETE => ['onPreDelete', 0],
            AudienceEvents::INSERT     => ['onInsert', 0],
            AudienceEvents::UPDATE     => ['onUpdate', 0],
            AudienceEvents::DELETE     => ['onDelete', 0],
        ];
    }
}
