<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Repository;

use Doctrine\ORM\EntityRepository;
use Ekyna\Component\Commerce\Stat\Entity\StockStat;
use Ekyna\Component\Commerce\Stat\Repository\StockStatRepositoryInterface;

/**
 * Class StockStatRepository
 * @package Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Repository
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class StockStatRepository extends EntityRepository implements StockStatRepositoryInterface
{
    /**
     * @inheritDoc
     */
    public function findOneByDay(\DateTime $date = null): ?StockStat
    {
        if (null === $date) {
            $date = new \DateTime();
        }

        return $this->findOneBy(['date' => $date->format('Y-m-d')]);
    }

    /**
     * @inheritDoc
     */
    public function findLatest(int $limit = 30): array
    {
        return $this->findBy([], ['date' => 'DESC'], $limit);
    }
}
