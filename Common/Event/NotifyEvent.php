<?php

namespace Ekyna\Component\Commerce\Common\Event;

use Ekyna\Component\Commerce\Common\Model\Notify;
use Symfony\Component\EventDispatcher\Event;

/**
 * Class NotifyEvent
 * @package Ekyna\Component\Commerce\Common\Event
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class NotifyEvent extends Event
{
    /**
     * @var Notify
     */
    private $notify;

    /**
     * @var bool
     */
    private $abort = false;


    /**
     * Constructor.
     *
     * @param Notify $notify
     */
    public function __construct(Notify $notify)
    {
        $this->notify = $notify;
    }

    /**
     * Returns the notify.
     *
     * @return Notify
     */
    public function getNotify()
    {
        return $this->notify;
    }

    /**
     * Returns whether notify has been abort.
     *
     * @return bool
     */
    public function isAbort()
    {
        return $this->abort;
    }

    /**
     * Sets the notify as abort.
     *
     * @return NotifyEvent
     */
    public function abort()
    {
        $this->stopPropagation();

        $this->abort = true;

        return $this;
    }
}
