<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Stat\Updater;

use DateTime;
use Decimal\Decimal;
use Ekyna\Component\Commerce\Stat\Entity\StockStat;
use Ekyna\Component\Commerce\Stat\Repository\StockStatRepositoryInterface;

/**
 * Class AbstractStockStatUpdater
 * @package Ekyna\Component\Commerce\Stat\Updater
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
abstract class AbstractStockStatUpdater
{
    public function update(): bool
    {
        $date = new DateTime();

        if (null !== $this->getStatRepository()->findOneByDay($date)) {
            return false;
        }

        $result = $this->calculateStockStats();

        $stat = $this->createStockStat();
        $stat
            ->setInValue(new Decimal($result['in_value'] ?? 0))
            ->setSoldValue(new Decimal($result['sold_value'] ?? 0))
            ->setDate($date->format('Y-m-d'));

        $this->persist($stat);

        return true;
    }

    abstract protected function calculateStockStats(): array;

    abstract protected function getStatRepository(): StockStatRepositoryInterface;

    abstract protected function persist(object $object): void;

    protected function createStockStat(): StockStat
    {
        return new StockStat();
    }
}
