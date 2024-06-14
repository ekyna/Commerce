<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Repository;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Ekyna\Component\Commerce\Order\Model\OrderShipmentItemInterface;
use Ekyna\Component\Commerce\Order\Repository\OrderShipmentItemRepositoryInterface;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentStates;
use Ekyna\Component\Commerce\Subject\Model\SubjectInterface;
use Ekyna\Component\Resource\Doctrine\ORM\Repository\ResourceRepository;
use Ekyna\Component\Resource\Model\DateRange;

/**
 * Class OrderShipmentItemRepository
 * @package Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Repository
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class OrderShipmentItemRepository extends ResourceRepository implements OrderShipmentItemRepositoryInterface
{
    private ?Query $findBySubject             = null;
    private ?Query $findBySubjectAndDateRange = null;

    /**
     * @return array<int, OrderShipmentItemInterface>
     */
    public function findBySubjectAndDateRange(SubjectInterface $subject, ?DateRange $range): array
    {
        if ($range) {
            return $this
                ->getFindBySubjectAndDateRangeQuery()
                ->setParameters([
                    'provider'   => $subject::getProviderName(),
                    'identifier' => $subject->getId(),
                ])
                ->setParameter('start', $range->getStart(), Types::DATETIME_MUTABLE)
                ->setParameter('end', $range->getEnd(), Types::DATETIME_MUTABLE)
                ->getResult();
        }

        return $this
            ->getFindBySubjectQuery()
            ->setParameters([
                'provider'   => $subject::getProviderName(),
                'identifier' => $subject->getId(),
            ])
            ->getResult();
    }

    private function getFindBySubjectQuery(): Query
    {
        if (null !== $this->findBySubject) {
            return $this->findBySubject;
        }

        return $this->findBySubject = $this
            ->createFindBySubjectQueryBuilder()
            ->getQuery();
    }

    private function getFindBySubjectAndDateRangeQuery(): Query
    {
        if (null !== $this->findBySubjectAndDateRange) {
            return $this->findBySubjectAndDateRange;
        }

        $qb = $this->createFindBySubjectQueryBuilder();

        return $this->findBySubjectAndDateRange = $qb
            ->andWhere($qb->expr()->between('s.shippedAt', ':start', ':end'))
            ->getQuery();
    }

    private function createFindBySubjectQueryBuilder(): QueryBuilder
    {
        $qb = $this->createQueryBuilder('i');

        return $qb
            ->join('i.orderItem', 'oi')
            ->join('i.shipment', 's')
            ->andWhere($qb->expr()->eq('oi.subjectIdentity.provider', ':provider'))
            ->andWhere($qb->expr()->eq('oi.subjectIdentity.identifier', ':identifier'))
            ->andWhere(
                $qb->expr()->in('s.state', [
                    ShipmentStates::STATE_SHIPPED,
                    ShipmentStates::STATE_RETURNED,
                ])
            );
    }
}
