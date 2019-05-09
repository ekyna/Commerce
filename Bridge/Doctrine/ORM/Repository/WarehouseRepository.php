<?php

namespace Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Repository;

use Ekyna\Component\Commerce\Common\Model\CountryInterface;
use Ekyna\Component\Commerce\Exception\RuntimeException;
use Ekyna\Component\Commerce\Stock\Model\WarehouseInterface;
use Ekyna\Component\Commerce\Stock\Repository\WarehouseRepositoryInterface;
use Ekyna\Component\Resource\Doctrine\ORM\ResourceRepository;

/**
 * Class WarehouseRepository
 * @package Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Repository
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class WarehouseRepository extends ResourceRepository implements WarehouseRepositoryInterface
{
    /**
     * @var WarehouseInterface
     */
    private $defaultWarehouse;


    /**
     * @inheritDoc
     */
    public function findDefault(): WarehouseInterface
    {
        if ($this->defaultWarehouse) {
            return $this->defaultWarehouse;
        }

        $qb = $this->createQueryBuilder('w');

        $warehouse = $qb
            ->andWhere($qb->expr()->eq('w.default', true))
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        if (null == $warehouse) {
            throw new RuntimeException('Default warehouse not found.');
        }

        return $this->defaultWarehouse = $warehouse;
    }

    /**
     * @inheritDoc
     */
    public function findOneByCountry(CountryInterface $country): ?WarehouseInterface
    {
        $qb = $this->createQueryBuilder('w');

        return $qb
            ->andWhere($qb->expr()->isMemberOf(':country', 'w.countries'))
            ->addOrderBy('w.priority', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->setParameter('country', $country)
            ->getOneOrNullResult();
    }
}
