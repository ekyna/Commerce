<?php

namespace Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Repository;

use Ekyna\Component\Commerce\Common\Model\CountryInterface;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentMethodInterface;
use Ekyna\Component\Commerce\Shipment\Repository\ShipmentPriceRepositoryInterface;
use Ekyna\Component\Resource\Doctrine\ORM\ResourceRepository;

/**
 * Class ShipmentPriceRepository
 * @package Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Repository
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ShipmentPriceRepository extends ResourceRepository implements ShipmentPriceRepositoryInterface
{
    /**
     * @var \Doctrine\ORM\Query
     */
    private $findOneByCountryAndMethodAndWeightQuery;

    /**
     * @inheritdoc
     */
    public function findOneByCountryAndMethodAndWeight(
        CountryInterface $country,
        ShipmentMethodInterface $method,
        $weight
    ) {
        return $this
            ->getFindOneByCountryAndMethodAndWeightQuery()
            ->setParameters([
                'country'   => $country,
                'method'    => $method,
                'weight'    => $weight,
//                'enabled'   => true,
//                'available' => true,
            ])
            ->getOneOrNullResult();
    }

    /**
     * Returns the "find one price by country, method and weight" query.
     *
     * @return \Doctrine\ORM\Query
     */
    private function getFindOneByCountryAndMethodAndWeightQuery()
    {
        if (null === $this->findOneByCountryAndMethodAndWeightQuery) {
            $qb = $this->getCollectionQueryBuilder();
            $qb
                ->join('o.zone', 'z')
                ->join('o.method', 'm')
                ->andWhere($qb->expr()->isMemberOf(':country', 'z.countries'))
                ->andWhere($qb->expr()->gte('o.weight', ':weight'))
                ->andWhere($qb->expr()->eq('o.method', ':method'))
//                ->andWhere($qb->expr()->eq('m.enabled', ':enabled'))
//                ->andWhere($qb->expr()->eq('m.available', ':available'))
                ->addGroupBy('m.id')
                ->addOrderBy('o.weight', 'ASC')
                ->setMaxResults(1);

            $this->findOneByCountryAndMethodAndWeightQuery = $qb->getQuery();
        }

        return $this->findOneByCountryAndMethodAndWeightQuery;
    }
}
