<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Stat\Updater;

use DateTime;
use Decimal\Decimal;
use Ekyna\Component\Commerce\Stat\Calculator\StatCalculatorInterface;
use Ekyna\Component\Commerce\Stat\Entity;
use Ekyna\Component\Commerce\Stat\Repository;

/**
 * Class StatUpdater
 * @package Ekyna\Component\Commerce\Stat\Updater
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
abstract class AbstractStatUpdater implements StatUpdaterInterface
{
    private StatCalculatorInterface $calculator;

    public function __construct(StatCalculatorInterface $calculator)
    {
        $this->calculator = $calculator;
    }

    public function updateStockStat(): bool
    {
        $date = new DateTime();

        if (null !== $this->getStockStatRepository()->findOneByDay($date)) {
            return false;
        }

        $result = $this->calculator->calculateStockStats();

        $stat = $this->createStockStat();
        $stat
            ->setInValue(new Decimal($result['in_value'] ?? 0))
            ->setSoldValue(new Decimal($result['sold_value'] ?? 0))
            ->setDate($date->format('Y-m-d'));

        $this->persist($stat);

        return true;
    }

    public function updateDayOrderStat(DateTime $date, bool $force = false): bool
    {
        if (null === $stat = $this->getOrderStatRepository()->findOneByDay($date)) {
            $stat = $this->createOrderStat();
            $stat
                ->setType(Entity\OrderStat::TYPE_DAY)
                ->setDate($date->format('Y-m-d'));
        }

        $result = $this->calculator->calculateDayOrderStats($date);

        if ($stat->loadResult($result) || $force) {
            $stat->setUpdatedAt(new DateTime());

            $this->persist($stat);

            return true;
        }

        return false;
    }

    public function updateMonthOrderStat(DateTime $date, bool $force = false): bool
    {
        if (null === $stat = $this->getOrderStatRepository()->findOneByMonth($date)) {
            $stat = $this->createOrderStat();
            $stat
                ->setType(Entity\OrderStat::TYPE_MONTH)
                ->setDate($date->format('Y-m'));
        }

        $result = $this->calculator->calculateMonthOrderStats($date);

        if ($stat->loadResult($result) || $force) {
            $stat->setUpdatedAt(new DateTime());

            $this->persist($stat);

            return true;
        }

        return false;
    }

    public function updateYearOrderStat(DateTime $date, bool $force = false): bool
    {
        if (null === $stat = $this->getOrderStatRepository()->findOneByYear($date)) {
            $stat = $this->createOrderStat();
            $stat
                ->setType(Entity\OrderStat::TYPE_YEAR)
                ->setDate($date->format('Y'));
        }

        $result = $this->calculator->calculateYearOrderStats($date);

        if ($stat->loadResult($result) || $force) {
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
     * Returns the stock stat repository.
     */
    abstract protected function getStockStatRepository(): Repository\StockStatRepositoryInterface;

    /**
     * Returns the order stat repository.
     */
    abstract protected function getOrderStatRepository(): Repository\OrderStatRepositoryInterface;

    /**
     * Returns a new stock stat entity.
     */
    protected function createStockStat(): Entity\StockStat
    {
        return new Entity\StockStat();
    }

    /**
     * Returns a new order stat entity.
     */
    protected function createOrderStat(): Entity\OrderStat
    {
        return new Entity\OrderStat();
    }
}
