<?php

namespace Ekyna\Component\Commerce\Common\Listener;

use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Common\Notify\NotifyBuilder;
use Ekyna\Component\Commerce\Common\Notify\NotifyQueue;
use Ekyna\Component\Resource\Model\ResourceInterface;
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
        $this->queue   = $queue;
        $this->builder = $builder;
    }

    /**
     * Returns whether the state of the given resource changed to the given state.
     *
     * @param ResourceInterface $resource
     * @param string            $state
     *
     * @return bool
     */
    protected function didStateChangeTo(ResourceInterface $resource, string $state): bool
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
     * Returns whether the sale has a notification with the given type and key number.
     *
     * @param SaleInterface $sale
     * @param string        $type
     * @param string        $key
     * @param string        $number
     *
     * @return bool
     */
    protected function hasNotification(SaleInterface $sale, string $type, string $key, string $number): bool
    {
        foreach ($sale->getNotifications() as $n) {
            if ($n->getType() !== $type) {
                continue;
            }

            if ($n->hasData($key) && $n->getData($key) === $number) {
                return true;
            }
        }

        return false;
    }

    /**
     * Creates, build and enqueue a notify instance.
     *
     * @param string            $type
     * @param ResourceInterface $resource
     */
    protected function notify(string $type, ResourceInterface $resource): void
    {
        // Create
        $notify = $this->builder->create($type, $resource);

        // Schedule
        $this->queue->schedule($notify);
    }
}
