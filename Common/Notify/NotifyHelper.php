<?php

namespace Ekyna\Component\Commerce\Common\Notify;

use Ekyna\Component\Resource\Model\ResourceInterface;

/**
 * Class NotifyHelper
 * @package Ekyna\Component\Commerce\Common\Notify
 * @author  Etienne Dauvergne <contact@ekyna.com>
 *
 * @TODO Remove as not used
 */
class NotifyHelper
{
    /**
     * @var NotifyBuilder
     */
    protected $builder;

    /**
     * @var NotifyQueue
     */
    protected $queue;


    /**
     * Constructor.
     *
     * @param NotifyBuilder $builder
     * @param NotifyQueue   $queue
     */
    public function __construct(NotifyBuilder $builder, NotifyQueue $queue)
    {
        $this->builder = $builder;
        $this->queue = $queue;
    }

    /**
     * Creates, builds and queue a notification for the given source and type.
     *
     * @param ResourceInterface $source
     * @param string            $type
     */
    public function buildAndQueue(ResourceInterface $source, $type)
    {
        // Create
        $notify = $this->builder->create($type, $source);

        // Build
        if ($this->builder->build($notify)) {
            return;
        }

        // Enqueue
        $this->queue->add($notify);
    }
}
