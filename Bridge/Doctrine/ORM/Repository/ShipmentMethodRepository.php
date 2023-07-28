<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Repository;

use Decimal\Decimal;
use Doctrine\ORM\Query;
use Ekyna\Component\Commerce\Common\Model\CountryInterface;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentMethodInterface;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentZoneInterface;
use Ekyna\Component\Commerce\Shipment\Repository\ShipmentMethodRepositoryInterface;
use Ekyna\Component\Resource\Doctrine\ORM\Repository\TranslatableRepository;

/**
 * Class ShipmentMethodRepository
 * @package Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Repository
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ShipmentMethodRepository extends TranslatableRepository implements ShipmentMethodRepositoryInterface
{
    private ?Query $findAvailableByCountryAndWeightQuery = null;

    public function findHavingPrices(ShipmentZoneInterface $zone = null): array
    {
        $qb = $this->getCollectionQueryBuilder();
        $qb
            ->join('o.prices', 'p')
            //->addGroupBy('o.id')
            ->addOrderBy('o.position', 'ASC');

        $parameters = [];

        if (null !== $zone) {
            $qb->andWhere($qb->expr()->eq('p.zone', ':zone'));
            $parameters['zone'] = $zone;
        }

        return $qb
            ->getQuery()
            ->setParameters($parameters)
            ->getResult();
    }

    public function findAvailableByCountryAndWeight(CountryInterface $country, Decimal $weight): array
    {
        return $this
            ->getFindAvailableByCountryAndWeightQuery()
            ->setParameters([
                'country'   => $country,
                'weight'    => $weight,
                'enabled'   => true,
                'available' => true,
            ])
            ->getResult();
    }

    public function findOneByPlatform(string $platformName): ?ShipmentMethodInterface
    {
        $qb = $this->createQueryBuilder('m');

        return $qb
            ->andWhere($qb->expr()->eq('m.platformName', ':name'))
            ->andWhere($qb->expr()->eq('m.enabled', ':enabled'))
            ->setMaxResults(1)
            ->getQuery()
            ->setParameters([
                'name'    => $platformName,
                'enabled' => true,
            ])
            ->getOneOrNullResult();
    }

    /**
     * Returns the "find available by country and weight" query.
     */
    private function getFindAvailableByCountryAndWeightQuery(): Query
    {
        if (null === $this->findAvailableByCountryAndWeightQuery) {
            $qb = $this->getCollectionQueryBuilder();

            $this->findAvailableByCountryAndWeightQuery = $qb
                ->join('o.prices', 'p')
                ->join('p.zone', 'z')
                ->andWhere($qb->expr()->isMemberOf(':country', 'z.countries'))
                ->andWhere($qb->expr()->gte('p.weight', ':weight'))
                ->andWhere($qb->expr()->eq('o.enabled', ':enabled'))
                ->andWhere($qb->expr()->eq('o.available', ':available'))
                ->addOrderBy('o.position', 'ASC')
                ->getQuery();
        }

        return $this->findAvailableByCountryAndWeightQuery;
    }
}
