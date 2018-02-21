<?php

namespace Ekyna\Component\Commerce\Stat\Updater;

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
    /**
     * @var StatCalculatorInterface
     */
    private $calculator;


    /**
     * Constructor.
     *
     * @param StatCalculatorInterface $calculator
     */
    public function __construct(StatCalculatorInterface $calculator)
    {
        $this->calculator = $calculator;
    }

    /**
     * @inheritdoc
     */
    public function updateStockStat()
    {
        $date = new \DateTime();

        if (null !== $stat = $this->getStockStatRepository()->findOneByDay($date)) {
            return false;
        }

        $result = $this->calculator->calculateStockStats();

        $stat = $this->createStockStat();
        $stat
            ->setInValue($result['in_value'] ?: 0)
            ->setSoldValue($result['sold_value'] ?: 0)
            ->setDate($date->format('Y-m-d'));

        $this->persist($stat);

        return true;
    }

    /**
     * @inheritdoc
     */
    public function updateDayOrderStat(\DateTime $date, $force = false)
    {
        if (null === $stat = $this->getOrderStatRepository()->findOneByDay($date)) {
            $stat = $this->createOrderStat();
            $stat
                ->setType(Entity\OrderStat::TYPE_DAY)
                ->setDate($date->format('Y-m-d'));
        }

        $result = $this->calculator->calculateDayOrderStats($date);

        if ($stat->loadResult($result) || $force) {
            $stat->setUpdatedAt(new \DateTime());

            $this->persist($stat);

            return true;
        }

        return false;
    }

    /**
     * @inheritdoc
     */
    public function updateMonthOrderStat(\DateTime $date, $force = false)
    {
        if (null === $stat = $this->getOrderStatRepository()->findOneByMonth($date)) {
            $stat = $this->createOrderStat();
            $stat
                ->setType(Entity\OrderStat::TYPE_MONTH)
                ->setDate($date->format('Y-m'));
        }

        $result = $this->calculator->calculateMonthOrderStats($date);

        if ($stat->loadResult($result) || $force) {
            $stat->setUpdatedAt(new \DateTime());

            $this->persist($stat);

            return true;
        }

        return false;
    }

    /**
     * @inheritdoc
     */
    public function updateYearOrderStat(\DateTime $date, $force = false)
    {
        if (null === $stat = $this->getOrderStatRepository()->findOneByYear($date)) {
            $stat = $this->createOrderStat();
            $stat
                ->setType(Entity\OrderStat::TYPE_YEAR)
                ->setDate($date->format('Y'));
        }

        $result = $this->calculator->calculateYearOrderStats($date);

        if ($stat->loadResult($result) || $force) {
            $stat->setUpdatedAt(new \DateTime());

            $this->persist($stat);

            return true;
        }

        return false;
    }

    /**
     * Persists the given object.
     *
     * @param object $object
     */
    abstract protected function persist($object);

    /**
     * Returns the stock stat repository.
     *
     * @return Repository\StockStatRepositoryInterface
     */
    abstract protected function getStockStatRepository();

    /**
     * Returns the order stat repository.
     *
     * @return Repository\OrderStatRepositoryInterface
     */
    abstract protected function getOrderStatRepository();

    /**
     * Returns a new stock stat entity.
     *
     * @return Entity\StockStat
     */
    protected function createStockStat()
    {
        return new Entity\StockStat();
    }

    /**
     * Returns a new order stat entity.
     *
     * @return Entity\OrderStat
     */
    protected function createOrderStat()
    {
        return new Entity\OrderStat();
    }
}