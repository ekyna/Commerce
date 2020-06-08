<?php

namespace Ekyna\Component\Commerce\Common\Listener;

use Ekyna\Component\Commerce\Common\Notify\NotifyBuilder;
use Ekyna\Component\Commerce\Common\Notify\NotifyQueue;
use Ekyna\Component\Resource\Persistence\PersistenceTrackerInterface;

/**
 * Class AbstractNotifyListener
 * @package Ekyna\Component\Commerce\Common\Listener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
abstract class AbstractNotifyListener
{
    /**
     * @var PersistenceTrackerInterface
     */
    protected $tracker;

    /**
     * @var NotifyQueue
     */
    protected $queue;

    /**
     * @var NotifyBuilder
     */
    protected $builder;


    /**
     * Constructor.
     *
     * @param PersistenceTrackerInterface $tracker
     * @param NotifyQueue                 $queue
     * @param NotifyBuilder               $builder
     */
    public function __construct(
        PersistenceTrackerInterface $tracker,
        NotifyQueue $queue,
        NotifyBuilder $builder
    ) {
        $this->tracker = $tracker;
        $this->queue = $queue;
        $this->builder = $builder;
    }

    /**
     * Returns whether the state of the given resource changed to the given state.
     *
     * @param object $resource
     * @param string $state
     *
     * @return bool
     */
    protected function didStateChangeTo($resource, $state)
    {
        if (empty($stateCs = $this->tracker->getChangeSet($resource, 'state'))) {
            return false;
        }

        if ($stateCs[1] === $state && $stateCs[0] !== $state) {
            return true;
        }

        return false;
    }

    /**
     * Creates, build and enqueue a notify instance.
     *
     * @param string $type
     * @param object $resource
     */
    protected function notify($type, $resource)
    {
        // Create
        $notify = $this->builder->create($type, $resource);

        // Schedule
        $this->queue->schedule($notify);
    }
}
