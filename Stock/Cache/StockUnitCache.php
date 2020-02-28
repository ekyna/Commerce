<?php

namespace Ekyna\Component\Commerce\Stock\Cache;

use Ekyna\Component\Commerce\Exception\LogicException;
use Ekyna\Component\Commerce\Stock\Model\StockSubjectInterface;
use Ekyna\Component\Commerce\Stock\Model\StockUnitInterface;
use Ekyna\Component\Resource\Event\QueueEvents;
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
     * [<subject hash> => StockUnitInterface[]]
     */
    protected $addedUnits;

    /**
     * @var StockUnitInterface[][]
     * [<subject hash> => StockUnitInterface[]]
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
    public function onEventQueueClose(): void
    {
        $this->clear();
    }

    /**
     * @inheritdoc
     */
    public function add(StockUnitInterface $unit): void
    {
        if (null === $subject = $unit->getSubject()) {
            throw new LogicException("Stock unit's subject must be set.");
        }

        $hash = $this->getSubjectHash($subject);

        // Clears the unit from the removed list
        $this->pop($this->removedUnits, $hash, $unit);

        // Adds the unit the added list
        $this->push($this->addedUnits, $hash, $unit);
    }

    /**
     * @inheritdoc
     */
    public function isAdded(StockUnitInterface $unit): bool
    {
        if (null === $subject = $unit->getSubject()) {
            throw new LogicException("Stock unit's subject must be set.");
        }

        return $this->has($this->addedUnits, $this->getSubjectHash($subject), $unit);
    }

    /**
     * @inheritdoc
     */
    public function remove(StockUnitInterface $unit): void
    {
        if (null === $subject = $unit->getSubject()) {
            throw new LogicException("Stock unit's subject must be set.");
        }

        $hash = $this->getSubjectHash($subject);

        // Clears the unit from the added list
        $this->pop($this->addedUnits, $hash, $unit);

        // Adds the unit the removed list
        $this->push($this->removedUnits, $hash, $unit);
    }

    /**
     * @inheritdoc
     */
    public function isRemoved(StockUnitInterface $unit): bool
    {
        if (null === $subject = $unit->getSubject()) {
            throw new LogicException("Stock unit's subject must be set.");
        }

        return $this->has($this->removedUnits, $this->getSubjectHash($subject), $unit);
    }

    /**
     * @inheritdoc
     */
    public function findAddedBySubject(StockSubjectInterface $subject): array
    {
        $hash = $this->getSubjectHash($subject);

        if (isset($this->addedUnits[$hash])) {
            return $this->addedUnits[$hash];
        }

        return [];
    }

    /**
     * @inheritdoc
     */
    public function findRemovedBySubject(StockSubjectInterface $subject): array
    {
        $hash = $this->getSubjectHash($subject);

        if (isset($this->removedUnits[$hash])) {
            return $this->removedUnits[$hash];
        }

        return [];
    }

    /**
     * Returns the subject hash.
     *
     * @param StockSubjectInterface $subject
     *
     * @return string
     */
    protected function getSubjectHash(StockSubjectInterface $subject): string
    {
        return spl_object_hash($subject);
    }

    /**
     * Returns whether the unit exists into the given list for the given subject hash.
     *
     * @param array              $list
     * @param string             $hash
     * @param StockUnitInterface $unit
     *
     * @return bool
     */
    private function has(array &$list, string $hash, StockUnitInterface $unit): bool
    {
        if (!isset($list[$hash])) {
            return false;
        }

        return null !== $this->find($list, $hash, $unit);
    }

    /**
     * Return the unit's index from the given list for the given subject hash.
     *
     * @param array              $list
     * @param string             $hash
     * @param StockUnitInterface $unit
     *
     * @return int|null
     */
    private function find(array &$list, string $hash, StockUnitInterface $unit): ?int
    {
        if (!isset($list[$hash])) {
            return null;
        }

        // Non persisted search
        if (null === $unit->getId()) {
            if (false !== $index = array_search($unit, $list[$hash], true)) {
                return (int)$index;
            }

            return null;
        }

        // Persisted search
        /** @var StockUnitInterface $u */
        foreach ($list[$hash] as $index => $u) {
            if ($u->getId() == $unit->getId()) {
                return $index;
            }
        }

        return null;
    }

    /**
     * Pushes the unit to the given list for the given subject hash.
     *
     * @param array              $list
     * @param string             $hash
     * @param StockUnitInterface $unit
     */
    private function push(array &$list, string $hash, StockUnitInterface $unit): void
    {
        if (!$this->has($list, $hash, $unit)) {
            $list[$hash][] = $unit;
        }
    }

    /**
     * Pops the unit to the given list for the given subject hash.
     *
     * @param array              $list
     * @param string             $hash
     * @param StockUnitInterface $unit
     */
    private function pop(array &$list, string $hash, StockUnitInterface $unit): void
    {
        if (false !== $index = $this->find($list, $hash, $unit)) {
            unset($list[$hash][$index]);

            if (empty($list[$hash])) {
                unset($list[$hash]);
            }
        }
    }

    /**
     * Clears the stock unit lists.
     */
    private function clear(): void
    {
        $this->addedUnits   = [];
        $this->removedUnits = [];
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
