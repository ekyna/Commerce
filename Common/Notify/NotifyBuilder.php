<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Common\Notify;

use Ekyna\Component\Commerce\Common\Event\NotifyEvent;
use Ekyna\Component\Commerce\Common\Event\NotifyEvents;
use Ekyna\Component\Commerce\Common\Model\Notify;
use Ekyna\Component\Resource\Model\ResourceInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Class NotifyBuilder
 * @package Ekyna\Component\Commerce\Common\Notify
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class NotifyBuilder
{
    protected EventDispatcherInterface $eventDispatcher;


    public function __construct(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    public function create(string $type, ?ResourceInterface $source): Notify
    {
        $notify = new Notify();
        $notify
            ->setType($type)
            ->setSource($source);

        return $notify;
    }

    /**
     * @return bool Whether the notification has been successfully built.
     */
    public function build(Notify $notify): bool
    {
        $event = new NotifyEvent($notify);

        $this->eventDispatcher->dispatch($event, NotifyEvents::BUILD);

        if ($notify->getRecipients()->isEmpty()) {
            return false;
        }

        return !$event->isAbort();
    }
}
