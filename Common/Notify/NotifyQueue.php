<?php

namespace Ekyna\Component\Commerce\Common\Notify;

use Ekyna\Component\Commerce\Common\Model\Notify;

/**
 * Class NotifyQueue
 * @package Ekyna\Component\Commerce\Common\Notify
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class NotifyQueue
{
    /**
     * @var array|Notify[]
     */
    private $queue;


    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->clear();
    }

    /**
     * Returns all the queued notify objects.
     *
     * @return array|Notify[]
     */
    public function all()
    {
        $queue = $this->queue;

        return $queue;
    }

    /**
     * Flushes (and clears) the queue.
     *
     * @return array|Notify[]
     */
    public function flush()
    {
        $queue = $this->queue;

        $this->clear();

        return $queue;
    }

    /**
     * Clears the queue.
     *
     * @return NotifyQueue
     */
    public function clear()
    {
        $this->queue = [];

        return $this;
    }

    /**
     * Adds the notify.
     *
     * @param Notify $notify
     *
     * @return NotifyQueue
     */
    public function add(Notify $notify)
    {
        $this->queue[] = $notify;

        return $this;
    }
}
