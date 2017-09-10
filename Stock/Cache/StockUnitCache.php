<?php

namespace Ekyna\Component\Commerce\Stock\Cache;

use Ekyna\Component\Commerce\Exception\LogicException;
use Ekyna\Component\Commerce\Stock\Model\StockSubjectInterface;
use Ekyna\Component\Commerce\Stock\Model\StockUnitInterface;
use Ekyna\Component\Commerce\Stock\Model\StockUnitStates;
use Ekyna\Component\Commerce\Subject\SubjectHelperInterface;
use Ekyna\Component\Resource\Event\EventQueueInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class StockUnitCache
 * @package Ekyna\Component\Commerce\Stock\Cache
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class StockUnitCache implements StockUnitCacheInterface, EventSubscriberInterface
{
    /**
     * @var SubjectHelperInterface
     */
    protected $subjectHelper;

    /**
     * @var StockUnitInterface[][]
     * [identity => StockUnitInterface]
     */
    private $stockUnits;


    /**
     * Constructor.
     *
     * @param SubjectHelperInterface $subjectHelper
     */
    public function __construct(SubjectHelperInterface $subjectHelper)
    {
        $this->subjectHelper = $subjectHelper;

        $this->clear();
    }

    /**
     * Event queue close handler.
     */
    public function onEventQueueClose()
    {
        $this->clear();
    }

    /**
     * @inheritdoc
     */
    public function add(StockUnitInterface $stockUnit)
    {
        if (null === $subject = $stockUnit->getSubject()) {
            throw new LogicException("Stock unit's subject must be set.");
        }

        $oid = spl_object_hash($subject);

        if (!isset($this->stockUnits[$oid])) {
            $this->stockUnits[$oid] = [];
        }

        $this->stockUnits[$oid][] = $stockUnit;
    }

    /**
     * @inheritdoc
     */
    public function remove(StockUnitInterface $stockUnit)
    {
        if (null === $subject = $stockUnit->getSubject()) {
            throw new LogicException("Stock unit's subject must be set.");
        }

        $oid = spl_object_hash($subject);

        if (!isset($this->stockUnits[$oid])) {
            return;
        }

        if (false !== $index = array_search($stockUnit, $this->stockUnits[$oid], true)) {
            unset($this->stockUnits[$oid][$index]);
        }
    }

    /**
     * @inheritdoc
     */
    public function findNewBySubject(StockSubjectInterface $subject)
    {
        return $this->findBySubjectAndStates($subject, [
            StockUnitStates::STATE_NEW
        ]);
    }

    /**
     * @inheritdoc
     */
    public function findPendingBySubject(StockSubjectInterface $subject)
    {
        return $this->findBySubjectAndStates($subject, [
            StockUnitStates::STATE_PENDING,
        ]);
    }

    /**
     * @inheritdoc
     */
    public function findPendingOrReadyBySubject(StockSubjectInterface $subject)
    {
        return $this->findBySubjectAndStates($subject, [
            StockUnitStates::STATE_PENDING,
            StockUnitStates::STATE_READY,
        ]);
    }

    /**
     * @inheritdoc
     */
    public function findNotClosedBySubject(StockSubjectInterface $subject)
    {
        return $this->findBySubjectAndStates($subject, [
            StockUnitStates::STATE_NEW,
            StockUnitStates::STATE_PENDING,
            StockUnitStates::STATE_READY,
        ]);
    }

    /**
     * @inheritdoc
     */
    public function findAssignableBySubject(StockSubjectInterface $subject)
    {
        $stockUnits = [];

        // Find by subject oid
        $oid = spl_object_hash($subject);
        if (isset($this->stockUnits[$oid])) {
            $stockUnits = $this->stockUnits[$oid];
        }

        // Filter by :
        // - Not yet linked to a supplier order
        // - Sold lower than ordered
        if (!empty($stockUnits)) {
            $stockUnits = array_filter($stockUnits, function(StockUnitInterface $stockUnit) {
                return null === $stockUnit->getSupplierOrderItem()
                    && $stockUnit->getSoldQuantity() < $stockUnit->getOrderedQuantity();
            });
        }

        return $stockUnits;
    }

    /**
     * @inheritdoc
     */
    public function findLinkableBySubject(StockSubjectInterface $subject)
    {
        $stockUnits = [];

        // Find by subject oid
        $oid = spl_object_hash($subject);
        if (isset($this->stockUnits[$oid])) {
            $stockUnits = $this->stockUnits[$oid];
        }

        // Filter by :
        // - Not yet linked to a supplier order
        if (!empty($stockUnits)) {
            $stockUnits = array_filter($stockUnits, function(StockUnitInterface $stockUnit) {
                return null === $stockUnit->getSupplierOrderItem();
            });
        }

        return $stockUnits;
    }

    /**
     * Finds the suck units by subject and states.
     *
     * @param StockSubjectInterface $subject
     * @param array                 $states
     *
     * @return array|StockUnitInterface[]
     */
    private function findBySubjectAndStates(StockSubjectInterface $subject, array $states = [])
    {
        $stockUnits = [];

        // Find by subject oid
        $oid = spl_object_hash($subject);
        if (isset($this->stockUnits[$oid])) {
            $stockUnits = $this->stockUnits[$oid];
        }

        // Filter by states
        if (!empty($stockUnits) && !empty($states)) {
            $stockUnits = array_filter($stockUnits, function(StockUnitInterface $stockUnit) use ($states) {
                return in_array($stockUnit->getState(), $states);
            });
        }

        return $stockUnits;
    }

    /**
     * Clears the stock unit list.
     */
    private function clear()
    {
        $this->stockUnits = [];
    }

    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents()
    {
        return [
            EventQueueInterface::QUEUE_CLOSE => ['onEventQueueClose', 0],
        ];
    }
}
