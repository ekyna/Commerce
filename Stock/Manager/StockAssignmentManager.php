<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Stock\Manager;

use Ekyna\Component\Commerce\Exception\LogicException;
use Ekyna\Component\Commerce\Stock\Cache\StockAssignmentCacheInterface;
use Ekyna\Component\Commerce\Stock\Model\AssignableInterface;
use Ekyna\Component\Commerce\Stock\Model\AssignmentInterface;
use Ekyna\Component\Commerce\Stock\Model\StockUnitInterface;
use Ekyna\Component\Resource\Event\QueueEvents;
use Ekyna\Component\Resource\Persistence\PersistenceHelperInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class StockAssignmentManager
 * @package Ekyna\Component\Commerce\Stock\Manager
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class StockAssignmentManager implements StockAssignmentManagerInterface, EventSubscriberInterface
{
    public function __construct(
        private readonly PersistenceHelperInterface    $persistenceHelper,
        private readonly StockAssignmentCacheInterface $assignmentCache,
    ) {
    }

    /**
     * @inheritDoc
     */
    public function persist(AssignmentInterface $assignment): void
    {
        // Do not persist
        if ($assignment->isEmpty()) {
            $this->remove($assignment);

            return;
        }

        $this->persistenceHelper->persistAndRecompute($assignment, false);
    }

    /**
     * @inheritDoc
     */
    public function remove(AssignmentInterface $assignment, bool $hard = false): void
    {
        if (!$assignment->isEmpty()) {
            throw new LogicException('Assignment must be empty to be removed.');
        }

        if (!$hard) {
            if ($assignment->isRemovalPrevented()) {
                $this->persistenceHelper->persistAndRecompute($assignment, false);

                return;
            }

            if (!is_null($assignment->getId())) {
                $this->assignmentCache->addRemoved($assignment);

                return;
            }
        }

        $assignment
            ->setAssignable(null)
            ->setStockUnit(null);

        $this->persistenceHelper->remove($assignment, false);
    }

    /**
     * @inheritDoc
     */
    public function create(AssignableInterface $assignable, StockUnitInterface $unit = null): AssignmentInterface
    {
        $assignment = null;

        if ($unit) {
            $assignment = $this->assignmentCache->findRemoved($unit, $assignable);
        }

        if (!$assignment) {
            $class = $assignable->getAssignmentClass();
            $assignment = new $class();
        }

        $assignment->setAssignable($assignable);

        if ($unit) {
            $assignment->setStockUnit($unit);
        }

        return $assignment;
    }

    /**
     * Event queue close event handler.
     */
    public function onEventQueueClose(): void
    {
        foreach ($this->assignmentCache->flush() as $assignment) {
            $this->persistenceHelper->remove($assignment, false);
        }
    }

    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents(): array
    {
        return [
            QueueEvents::QUEUE_CLOSE => ['onEventQueueClose', 0],
        ];
    }
}
