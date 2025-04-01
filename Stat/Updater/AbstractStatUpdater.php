<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Stat\Updater;

use DateTime;
use Ekyna\Component\Commerce\Stat\Calculator\StatCalculatorInterface;
use Ekyna\Component\Commerce\Stat\Model\StatInterface;
use Ekyna\Component\Commerce\Stat\Repository\StatRepositoryInterface;
use Ekyna\Component\Commerce\Stat\StatHelperInterface;

/**
 * Class StatUpdater
 * @package Ekyna\Component\Commerce\Stat\Updater
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
abstract class AbstractStatUpdater implements StatUpdaterInterface
{
    private array $cache = [];

    public function __construct(
        protected readonly StatCalculatorInterface $calculator,
        protected readonly StatHelperInterface $helper,
    ) {
    }

    public function updateDayOrderStat(DateTime $date, bool $force = false): bool
    {
        $stat = $this->findDayStat($date);

        $result = $this->calculator->calculateDayStats($date);

        return $this->updateAndPersist($stat, $result, $force);
    }

    public function updateMonthOrderStat(DateTime $date, bool $force = false): bool
    {
        $stat = $this->findMonthStat($date);

        $result = $this->calculator->calculateMonthStats($date);

        return $this->updateAndPersist($stat, $result, $force);
    }

    public function updateYearOrderStat(string $year, bool $force = false): bool
    {
        $stat = $this->findYearStat($year);

        $result = $this->calculator->calculateYearStats($year);

        return $this->updateAndPersist($stat, $result, $force);
    }

    private function findDayStat(DateTime $date): StatInterface
    {
        if (isset($this->cache[$key = $date->format('Y-m-d')])) {
            return $this->cache[$key];
        }

        if (null === $stat = $this->getRepository()->findOneByDay($date)) {
            $stat = $this->createNewStat();
            $stat
                ->setType(StatInterface::TYPE_DAY)
                ->setDate($key);
        }

        return $this->cache[$key] = $stat;
    }

    private function findMonthStat(DateTime $date): StatInterface
    {
        if (isset($this->cache[$key = $date->format('Y-m')])) {
            return $this->cache[$key];
        }

        if (null === $stat = $this->getRepository()->findOneByMonth($date)) {
            $stat = $this->createNewStat();
            $stat
                ->setType(StatInterface::TYPE_MONTH)
                ->setDate($key);
        }

        return $this->cache[$key] = $stat;
    }

    private function findYearStat(string $year): StatInterface
    {
        if (isset($this->cache[$year])) {
            return $this->cache[$year];
        }

        if (null === $stat = $this->getRepository()->findOneByYear($year)) {
            $stat = $this->createNewStat();
            $stat
                ->setType(StatInterface::TYPE_YEAR)
                ->setDate($year);
        }

        return $this->cache[$year] = $stat;
    }

    private function updateAndPersist(StatInterface $stat, array $data, bool $force): bool
    {
        if ($stat->loadResult($data) || $force) {
            $stat->setUpdatedAt(new DateTime());

            $this->persist($stat);

            return true;
        }

        return false;
    }

    /**
     * Persists the given object.
     */
    abstract protected function persist(object $object): void;

    /**
     * Returns the order stat repository.
     */
    abstract protected function getRepository(): StatRepositoryInterface;

    /**
     * Returns a new order stat entity.
     */
    abstract protected function createNewStat(): StatInterface;
}
