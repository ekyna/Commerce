<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Repository;

use Ekyna\Component\Commerce\Shipment\Model\ShipmentMethodInterface;
use Ekyna\Component\Commerce\Shipment\Repository\ShipmentZoneRepositoryInterface;
use Ekyna\Component\Resource\Doctrine\ORM\Repository\ResourceRepository;

/**
 * Class ShipmentZoneRepository
 * @package Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Repository
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ShipmentZoneRepository extends ResourceRepository implements ShipmentZoneRepositoryInterface
{
    /**
     * @inheritDoc
     */
    public function findHavingPrices(ShipmentMethodInterface $method = null): array
    {
        $qb = $this->getCollectionQueryBuilder();
        $qb
            ->join('o.prices', 'p')
            ->addGroupBy('o.id');

        $parameters = [];

        if (null !== $method) {
            $qb->andWhere($qb->expr()->eq('p.method', ':method'));
            $parameters['method'] = $method;
        }

        return $qb
            ->getQuery()
            ->setParameters($parameters)
            ->getResult();
    }
}
