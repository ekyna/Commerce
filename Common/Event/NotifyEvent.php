<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Common\Event;

use Ekyna\Component\Commerce\Common\Model\Notify;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * Class NotifyEvent
 * @package Ekyna\Component\Commerce\Common\Event
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class NotifyEvent extends Event
{
    private Notify $notify;
    private bool $abort = false;


    public function __construct(Notify $notify)
    {
        $this->notify = $notify;
    }

    /**
     * Returns the notify.
     *
     * @return Notify
     */
    public function getNotify(): Notify
    {
        return $this->notify;
    }

    /**
     * Returns whether notify has been abort.
     *
     * @return bool
     */
    public function isAbort(): bool
    {
        return $this->abort;
    }

    /**
     * Sets the notify as abort.
     */
    public function abort(): void
    {
        $this->stopPropagation();

        $this->abort = true;
    }
}
