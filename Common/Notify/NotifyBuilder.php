<?php

namespace Ekyna\Component\Commerce\Common\Notify;

use Ekyna\Component\Commerce\Common\Event\NotifyEvent;
use Ekyna\Component\Commerce\Common\Event\NotifyEvents;
use Ekyna\Component\Commerce\Common\Model\Notify;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Class NotifyBuilder
 * @package Ekyna\Component\Commerce\Common\Notify
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class NotifyBuilder
{
    /**
     * @var EventDispatcherInterface
     */
    protected $eventDispatcher;


    /**
     * Constructor.
     *
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * Creates a notify.
     *
     * @param string $type
     * @param mixed  $source
     *
     * @return Notify
     */
    public function create($type, $source = null)
    {
        $notify = new Notify();
        $notify
            ->setType($type)
            ->setSource($source);

        return $notify;
    }

    /**
     * Builds the notify.
     *
     * @param \Ekyna\Component\Commerce\Common\Model\Notify $notify
     *
     * @return bool Whether the notify has been successfully built.
     */
    public function build(Notify $notify)
    {
        $event = new NotifyEvent($notify);

        $this->eventDispatcher->dispatch(NotifyEvents::BUILD, $event);

        return !$event->isAbort();
    }
}
