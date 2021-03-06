<?php

namespace Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Repository;

use Ekyna\Component\Commerce\Shipment\Repository\RelayPointRepositoryInterface;
use Ekyna\Component\Resource\Doctrine\ORM\ResourceRepository;

/**
 * Class RelayPointRepository
 * @package Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Repository
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class RelayPointRepository extends ResourceRepository implements RelayPointRepositoryInterface
{
    /**
     * @inheritDoc
     */
    public function findOneByNumberAndPlatform(string $number, string $platform)
    {
        $alias = $this->getAlias();
        $qb = $this->getQueryBuilder();

        return $qb
            ->andWhere($qb->expr()->eq($alias . '.number', ':number'))
            ->andWhere($qb->expr()->eq($alias . '.platformName', ':platform'))
            ->getQuery()
            ->useQueryCache(true)
            ->setParameters([
                'number'   => $number,
                'platform' => $platform,
            ])
            ->getOneOrNullResult();
    }

    /**
     * @inheritDoc
     */
    protected function getAlias()
    {
        return 'rp';
    }
}
