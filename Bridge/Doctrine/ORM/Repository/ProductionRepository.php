<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Repository;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Ekyna\Component\Commerce\Manufacture\Repository\ProductionRepositoryInterface;
use Ekyna\Component\Commerce\Subject\Model\SubjectInterface;
use Ekyna\Component\Resource\Doctrine\ORM\Repository\ResourceRepository;
use Ekyna\Component\Resource\Model\DateRange;

/**
 * Class ProductionRepository
 * @package Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Repository
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ProductionRepository extends ResourceRepository implements ProductionRepositoryInterface
{
    private ?Query $findBySubject             = null;
    private ?Query $findBySubjectAndDateRange = null;
    private ?Query $findByComponent             = null;
    private ?Query $findByComponentAndDateRange = null;

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

    public function findByComponentAndDateRange(SubjectInterface $subject, ?DateRange $range): array
    {
        if ($range) {
            return $this
                ->getFindByComponentAndDateRangeQuery()
                ->setParameters([
                    'provider'   => $subject::getProviderName(),
                    'identifier' => $subject->getId(),
                ])
                ->setParameter('start', $range->getStart(), Types::DATETIME_MUTABLE)
                ->setParameter('end', $range->getEnd(), Types::DATETIME_MUTABLE)
                ->getResult();
        }

        return $this
            ->getFindByComponentQuery()
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

    private function getFindByComponentQuery(): Query
    {
        if (null !== $this->findByComponent) {
            return $this->findByComponent;
        }

        return $this->findByComponent = $this
            ->createFindByComponentQueryBuilder()
            ->getQuery();
    }

    private function getFindBySubjectAndDateRangeQuery(): Query
    {
        if (null !== $this->findBySubjectAndDateRange) {
            return $this->findBySubjectAndDateRange;
        }

        $qb = $this->createFindBySubjectQueryBuilder();

        return $this->findBySubjectAndDateRange = $qb
            ->andWhere($qb->expr()->between('p.createdAt', ':start', ':end'))
            ->getQuery();
    }

    private function getFindByComponentAndDateRangeQuery(): Query
    {
        if (null !== $this->findByComponentAndDateRange) {
            return $this->findByComponentAndDateRange;
        }

        $qb = $this->createFindByComponentQueryBuilder();

        return $this->findByComponentAndDateRange = $qb
            ->andWhere($qb->expr()->between('p.createdAt', ':start', ':end'))
            ->getQuery();
    }

    private function createFindBySubjectQueryBuilder(): QueryBuilder
    {
        $qb = $this->createQueryBuilder('p');

        return $qb
            ->join('p.productionOrder', 'o')
            ->andWhere($qb->expr()->eq('o.subjectIdentity.provider', ':provider'))
            ->andWhere($qb->expr()->eq('o.subjectIdentity.identifier', ':identifier'));
    }

    private function createFindByComponentQueryBuilder(): QueryBuilder
    {
        $qb = $this->createQueryBuilder('p');

        return $qb
            ->join('p.productionOrder', 'o')
            ->join('o.items', 'i')
            ->andWhere($qb->expr()->eq('i.subjectIdentity.provider', ':provider'))
            ->andWhere($qb->expr()->eq('i.subjectIdentity.identifier', ':identifier'));
    }
}
