<?php

namespace Ekyna\Component\Commerce\Stock\Manager;

use Ekyna\Component\Commerce\Common\Factory\SaleFactoryInterface;
use Ekyna\Component\Commerce\Common\Model\SaleItemInterface;
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
     * @var SaleFactoryInterface
     */
    private $saleFactory;


    /**
     * Constructor.
     *
     * @param PersistenceHelperInterface    $persistenceHelper
     * @param StockAssignmentCacheInterface $assignmentCache
     * @param SaleFactoryInterface          $saleFactory
     */
    public function __construct(
        PersistenceHelperInterface $persistenceHelper,
        StockAssignmentCacheInterface $assignmentCache,
        SaleFactoryInterface $saleFactory
    ) {
        $this->persistenceHelper = $persistenceHelper;
        $this->assignmentCache = $assignmentCache;
        $this->saleFactory = $saleFactory;
    }

    /**
     * @inheritDoc
     */
    public function persist(StockAssignmentInterface $assignment): void
    {
        $this->persistenceHelper->persistAndRecompute($assignment, false);
    }

    /**
     * @inheritDoc
     */
    public function remove(StockAssignmentInterface $assignment): void
    {
        $assignment
            ->setSoldQuantity(0)
            ->setShippedQuantity(0);

        if ($assignment->getId()) {
            $this->assignmentCache->remove($assignment);
            return;
        }

        $assignment
            ->setSaleItem(null)
            ->setStockUnit(null);

        $this->persistenceHelper->remove($assignment, false);
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
            $assignment = $this->saleFactory->createStockAssignmentForItem($item);
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