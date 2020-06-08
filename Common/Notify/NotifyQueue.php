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
     * @var Notify[]
     */
    private $scheduled;

    /**
     * @var Notify[]
     */
    private $queued;

    /**
     * @var NotifyBuilder
     */
    private $builder;


    /**
     * Constructor.
     *
     * @param NotifyBuilder $builder
     */
    public function __construct(NotifyBuilder $builder)
    {
        $this->builder = $builder;

        $this->clear();
    }

    /**
     * Clears the queue.
     *
     * @return NotifyQueue
     */
    public function clear(): self
    {
        $this->scheduled = [];
        $this->queued = [];

        return $this;
    }

    /**
     * Schedules the notification.
     *
     * @param Notify $notify
     *
     * @return $this
     */
    public function schedule(Notify $notify): self
    {
        $this->scheduled[] = $notify;

        return $this;
    }

    /**
     * Enqueue the notification.
     *
     * @param Notify $notify
     *
     * @return $this
     */
    public function enqueue(Notify $notify): self
    {
        $this->queued[] = $notify;

        return $this;
    }

    /**
     * Builds the scheduled notifications, and flushes (and clears) the queued notifications.
     *
     * @return Notify[]
     */
    public function flush(): array
    {
        $this->build();

        $queue = $this->queued;

        $this->clear();

        return $queue;
    }

    /**
     * Builds the scheduled notifications.
     */
    private function build(): void
    {
        if (empty($this->scheduled)) {
            return;
        }

        foreach ($this->scheduled as $notify) {
            if (!$this->builder->build($notify)) {
                continue;
            }

            $this->enqueue($notify);
        }

        $this->scheduled = [];
    }
}
