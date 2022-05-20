<?php

namespace Ekyna\Component\Commerce\Stock\Manager;

use Ekyna\Component\Commerce\Common\Helper\FactoryHelperInterface;
use Ekyna\Component\Commerce\Common\Model\SaleItemInterface;
use Ekyna\Component\Commerce\Exception\LogicException;
use Ekyna\Component\Commerce\Order\Model\OrderStates;
use Ekyna\Component\Commerce\Stock\Cache\StockAssignmentCacheInterface;
use Ekyna\Component\Commerce\Stock\Model\StockAssignmentInterface;
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
    /**
     * @var PersistenceHelperInterface
     */
    private $persistenceHelper;

    /**
     * @var StockAssignmentCacheInterface
     */
    private $assignmentCache;

    /**
     * @var FactoryHelperInterface
     */
    private $factoryHelper;

    public function __construct(
        PersistenceHelperInterface    $persistenceHelper,
        StockAssignmentCacheInterface $assignmentCache,
        FactoryHelperInterface        $factoryHelper
    ) {
        $this->persistenceHelper = $persistenceHelper;
        $this->assignmentCache = $assignmentCache;
        $this->factoryHelper = $factoryHelper;
    }

    /**
     * @inheritDoc
     */
    public function persist(StockAssignmentInterface $assignment): void
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
    public function remove(StockAssignmentInterface $assignment, bool $hard = false): void
    {
        if (!$assignment->isEmpty()) {
            throw new LogicException("Assignment must be empty to be removed.");
        }

        if (!$hard) {
            if ($this->isRemovalPrevented($assignment)) {
                $this->persistenceHelper->persistAndRecompute($assignment, false);

                return;
            }

            if (!is_null($assignment->getId())) {
                $this->assignmentCache->addRemoved($assignment);

                return;
            }
        }

        $assignment
            ->setSaleItem(null)
            ->setStockUnit(null);

        $this->persistenceHelper->remove($assignment, false);
    }

    /**
     * Returns whether the assignment should not be removed.
     *
     * @param StockAssignmentInterface $assignment
     *
     * @return bool
     */
    private function isRemovalPrevented(StockAssignmentInterface $assignment): bool
    {
        if (!$item = $assignment->getSaleItem()) {
            return false;
        }

        if (!$sale = $item->getRootSale()) {
            return false;
        }

        if (!OrderStates::isStockableState($sale->getState())) {
            return false;
        }

        return 1 >= $item->getStockAssignments()->count();
    }

    /**
     * @inheritDoc
     */
    public function create(SaleItemInterface $item, StockUnitInterface $unit = null): StockAssignmentInterface
    {
        $assignment = null;

        if ($unit) {
            $assignment = $this->assignmentCache->findRemoved($unit, $item);
        }

        if (!$assignment) {
            $assignment = $this->factoryHelper->createStockAssignmentForItem($item);
        }

        $assignment->setSaleItem($item);

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
    public static function getSubscribedEvents()
    {
        return [
            QueueEvents::QUEUE_CLOSE => ['onEventQueueClose', 0],
        ];
    }
}
