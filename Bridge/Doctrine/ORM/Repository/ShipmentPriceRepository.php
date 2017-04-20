<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Repository;

use Decimal\Decimal;
use Doctrine\ORM\Query;
use Ekyna\Component\Commerce\Common\Model\CountryInterface;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentMethodInterface;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentPriceInterface;
use Ekyna\Component\Commerce\Shipment\Repository\ShipmentPriceRepositoryInterface;
use Ekyna\Component\Resource\Doctrine\ORM\Repository\ResourceRepository;

/**
 * Class ShipmentPriceRepository
 * @package Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Repository
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ShipmentPriceRepository extends ResourceRepository implements ShipmentPriceRepositoryInterface
{
    private ?Query $findOneByCountryAndMethodAndWeightQuery = null;

    public function findOneByCountryAndMethodAndWeight(
        CountryInterface $country,
        ShipmentMethodInterface $method,
        Decimal $weight
    ): ShipmentPriceInterface {
        return $this
            ->getFindOneByCountryAndMethodAndWeightQuery()
            ->setParameters([
                'country' => $country,
                'method'  => $method,
                'weight'  => $weight,
//                'enabled'   => true,
//                'available' => true,
            ])
            ->getOneOrNullResult();
    }

    public function findByCountryAndWeight(CountryInterface $country, Decimal $weight, bool $available = true): array
    {
        $qb = $this->getCollectionQueryBuilder('o');
        $qb
            ->join('o.zone', 'z')
            ->join('o.method', 'm')
            ->andWhere($qb->expr()->isMemberOf(':country', 'z.countries'))
            ->andWhere($qb->expr()->gte('o.weight', ':weight'))
            ->andWhere($qb->expr()->eq('m.enabled', ':enabled'))
            ->addOrderBy('o.weight', 'ASC');

        $parameters = [
            'country' => $country,
            'weight'  => $weight,
            'enabled' => true,
        ];

        if ($available) {
            $qb->andWhere($qb->expr()->eq('m.available', ':available'));

            $parameters['available'] = true;
        }

        $prices = $qb
            ->getQuery()
            ->setParameters($parameters)
            ->getResult();

        $result = [];
        $methodIds = [];

        /** @var ShipmentPriceInterface $price */
        foreach ($prices as $price) {
            $methodId = intval($price->getMethod()->getId());

            if (in_array($methodId, $methodIds, true)) {
                continue;
            }

            $methodIds[] = $methodId;
            $result[] = $price;
        }

        usort($result, function (ShipmentPriceInterface $a, ShipmentPriceInterface $b) {
            $aPos = $a->getMethod()->getPosition();
            $bPos = $b->getMethod()->getPosition();

            if ($aPos == $bPos) {
                return 0;
            }

            return $aPos > $bPos ? 1 : -1;
        });

        return $result;
    }

    public function findByCountry(CountryInterface $country): array
    {
        $qb = $this->getCollectionQueryBuilder('o');
        $qb
            ->join('o.zone', 'z')
            ->join('o.method', 'm')
            ->andWhere($qb->expr()->isMemberOf(':country', 'z.countries'))
            ->andWhere($qb->expr()->eq('m.enabled', ':enabled'))
            ->addOrderBy('m.position', 'ASC')
            ->addOrderBy('o.weight', 'DESC');

        $parameters = [
            'country' => $country,
            'enabled' => true,
        ];

        return $qb
            ->getQuery()
            ->setParameters($parameters)
            ->getResult();
    }

    /**
     * Returns the "find one price by country, method and weight" query.
     */
    private function getFindOneByCountryAndMethodAndWeightQuery(): Query
    {
        if (null === $this->findOneByCountryAndMethodAndWeightQuery) {
            $qb = $this->getCollectionQueryBuilder('o');
            $qb
                ->join('o.zone', 'z')
                ->join('o.method', 'm')
                ->andWhere($qb->expr()->isMemberOf(':country', 'z.countries'))
                ->andWhere($qb->expr()->gte('o.weight', ':weight'))
                ->andWhere($qb->expr()->eq('o.method', ':method'))
                ->addOrderBy('o.weight', 'ASC')
                ->setMaxResults(1);

            $this->findOneByCountryAndMethodAndWeightQuery = $qb->getQuery();
        }

        return $this->findOneByCountryAndMethodAndWeightQuery;
    }
}
