<?php

namespace Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Repository;

use Ekyna\Component\Commerce\Common\Model\CountryInterface;
use Ekyna\Component\Commerce\Shipment\Entity\ShipmentMessage;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentStates;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentZoneInterface;
use Ekyna\Component\Commerce\Shipment\Repository\ShipmentMethodRepositoryInterface;
use Ekyna\Component\Resource\Doctrine\ORM\TranslatableResourceRepository;

/**
 * Class ShipmentMethodRepository
 * @package Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Repository
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ShipmentMethodRepository extends TranslatableResourceRepository implements ShipmentMethodRepositoryInterface
{
    /**
     * @var \Doctrine\ORM\Query
     */
    private $findAvailableByCountryAndWeightQuery;


    /**
     * {@inheritdoc}
     */
    public function createNew()
    {
        /** @var \Ekyna\Component\Commerce\Shipment\Model\ShipmentMethodInterface $method */
        $method = parent::createNew();

        foreach (ShipmentStates::getNotifiableStates() as $state) {
            $message = new ShipmentMessage();
            $method->addMessage($message->setState($state));
        }

        return $method;
    }

    /**
     * @inheritdoc
     */
    public function findHavingPrices(ShipmentZoneInterface $zone = null)
    {
        $qb = $this->getCollectionQueryBuilder();
        $qb
            ->join('o.prices', 'p')
            ->addGroupBy('o.id');

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

    /**
     * @inheritdoc
     */
    public function findAvailableByCountryAndWeight(CountryInterface $country, $weight)
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

    /**
     * Returns the "find available by country and weight" query.
     *
     * @return \Doctrine\ORM\Query
     */
    private function getFindAvailableByCountryAndWeightQuery()
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
                ->addGroupBy('o.id')
                ->getQuery();
        }

        return $this->findAvailableByCountryAndWeightQuery;
    }
}
