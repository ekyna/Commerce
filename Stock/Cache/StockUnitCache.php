<?php

namespace Ekyna\Component\Commerce\Stock\Cache;

use Ekyna\Component\Commerce\Exception\LogicException;
use Ekyna\Component\Commerce\Stock\Model\StockSubjectInterface;
use Ekyna\Component\Commerce\Stock\Model\StockUnitInterface;
use Ekyna\Component\Commerce\Stock\Model\StockUnitStates;
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
     * @var StockUnitInterface[][]
     * [identity => StockUnitInterface]
     */
    protected $addedUnits;

    /**
     * @var StockUnitInterface[][]
     * [identity => StockUnitInterface]
     */
    protected $removedUnits;


    /**
     * Constructor.
     */
    public function __construct()
    {
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
    public function add(StockUnitInterface $unit)
    {
        if (null === $subject = $unit->getSubject()) {
            throw new LogicException("Stock unit's subject must be set.");
        }

        $oid = spl_object_hash($subject);

        // Clears the unit from the removed list
        $this->pop($this->removedUnits, $oid, $unit);

        // Adds the unit the added list
        $this->push($this->addedUnits, $oid, $unit);
    }

    /**
     * @inheritdoc
     */
    public function isAdded(StockUnitInterface $unit)
    {
        if (null === $subject = $unit->getSubject()) {
            throw new LogicException("Stock unit's subject must be set.");
        }

        $oid = spl_object_hash($subject);

        return $this->has($this->addedUnits, $oid, $unit);
    }

    /**
     * @inheritdoc
     */
    public function remove(StockUnitInterface $unit)
    {
        if (null === $subject = $unit->getSubject()) {
            throw new LogicException("Stock unit's subject must be set.");
        }

        $oid = spl_object_hash($subject);

        // Clears the unit from the added list
        $this->pop($this->addedUnits, $oid, $unit);

        // Adds the unit the removed list
        $this->push($this->removedUnits, $oid, $unit);
    }

    /**
     * @inheritdoc
     */
    public function isRemoved(StockUnitInterface $unit)
    {
        if (null === $subject = $unit->getSubject()) {
            throw new LogicException("Stock unit's subject must be set.");
        }

        $oid = spl_object_hash($subject);

        return $this->has($this->removedUnits, $oid, $unit);
    }

    /**
     * @inheritdoc
     */
    public function findNewBySubject(StockSubjectInterface $subject)
    {
        return $this->findBySubjectAndStates($subject, [
            StockUnitStates::STATE_NEW,
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
    public function findReadyBySubject(StockSubjectInterface $subject)
    {
        return $this->findBySubjectAndStates($subject, [
            StockUnitStates::STATE_READY,
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
        $units = [];

        // Find by subject oid
        $oid = spl_object_hash($subject);
        if (isset($this->addedUnits[$oid])) {
            $units = $this->addedUnits[$oid];
        }

        // Filter by :
        // - Not yet linked to a supplier order
        // - Sold lower than ordered
        if (!empty($units)) {
            $units = array_filter($units, function (StockUnitInterface $unit) {
                return null === $unit->getSupplierOrderItem()
                    || ($unit->getSoldQuantity() < $unit->getOrderedQuantity() + $unit->getAdjustedQuantity());
            });
        }

        return $units;
    }

    /**
     * @inheritdoc
     */
    public function findLinkableBySubject(StockSubjectInterface $subject)
    {
        $units = [];

        // Find by subject oid
        $oid = spl_object_hash($subject);
        if (isset($this->addedUnits[$oid])) {
            $units = $this->addedUnits[$oid];
        }

        // Filter by :
        // - Not yet linked to a supplier order
        if (!empty($units)) {
            $units = array_filter($units, function (StockUnitInterface $unit) {
                return null === $unit->getSupplierOrderItem();
            });
        }

        return $units;
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
        $units = [];

        // Find by subject oid
        $oid = spl_object_hash($subject);
        if (isset($this->addedUnits[$oid])) {
            $units = $this->addedUnits[$oid];
        }

        // Filter by states
        if (!empty($units) && !empty($states)) {
            $units = array_filter($units, function (StockUnitInterface $unit) use ($states) {
                return in_array($unit->getState(), $states);
            });
        }

        return $units;
    }

    /**
     * Returns whether the unit exists into the given list for the given object identifier.
     *
     * @param array              $list
     * @param string             $oid
     * @param StockUnitInterface $unit
     *
     * @return bool
     */
    private function has(array &$list, $oid, StockUnitInterface $unit)
    {
        if (!isset($list[$oid])) {
            return false;
        }

        return false !== $this->find($list, $oid, $unit);
    }

    /**
     * Finds the unit into the given list for the given object identifier.
     *
     * @param array              $list
     * @param string             $oid
     * @param StockUnitInterface $unit
     *
     * @return bool|false|int|string
     */
    private function find(array &$list, $oid, StockUnitInterface $unit)
    {
        if (!isset($list[$oid])) {
            return false;
        }

        // Non persisted search
        if (null === $unit->getId()) {
            return array_search($unit, $list[$oid], true);
        }

        // Persisted search
        /** @var StockUnitInterface $u */
        foreach ($list[$oid] as $index => $u) {
            if ($u->getId() == $unit->getId()) {
                return $index;
            }
        }

        return false;
    }

    /**
     * Pushes the unit to the given list for the given object identifier.
     *
     * @param array              $list
     * @param string             $oid
     * @param StockUnitInterface $unit
     */
    private function push(array &$list, $oid, StockUnitInterface $unit)
    {
        if (!$this->has($list, $oid, $unit)) {
            $list[$oid][] = $unit;
        }
    }

    /**
     * Pops the unit to the given list for the given object identifier.
     *
     * @param array              $list
     * @param string             $oid
     * @param StockUnitInterface $unit
     */
    private function pop(array &$list, $oid, StockUnitInterface $unit)
    {
        if (false !== $index = $this->find($list, $oid, $unit)) {
            unset($list[$oid][$index]);

            if (empty($list[$oid])) {
                unset($list[$oid]);
            }
        }
    }

    /**
     * Clears the stock unit lists.
     */
    private function clear()
    {
        $this->addedUnits = [];
        $this->removedUnits = [];
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
