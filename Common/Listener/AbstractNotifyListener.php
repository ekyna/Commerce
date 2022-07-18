<?php

declare(strict_types=1);

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
    public function __construct(
        protected readonly PersistenceTrackerInterface $tracker,
        protected readonly NotifyQueue $queue,
        protected readonly NotifyBuilder $builder
    ) {

    }

    /**
     * Returns whether the state of the given resource changed to the given state.
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
     */
    protected function hasNotification(SaleInterface $sale, string $type, string $key, string $number): bool
    {
        foreach ($sale->getNotifications() as $n) {
            if ($n->getType() !== $type) {
                continue;
            }

            if ($n->getDatum($key) === $number) {
                return true;
            }
        }

        return false;
    }

    /**
     * Creates, build and enqueue a notify instance.
     */
    protected function notify(string $type, ResourceInterface $resource): void
    {
        // Create
        $notify = $this->builder->create($type, $resource);

        // Schedule
        $this->queue->schedule($notify);
    }
}
