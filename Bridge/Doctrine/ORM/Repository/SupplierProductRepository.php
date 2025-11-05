<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Repository;

use DateTime;
use DateTimeInterface;
use Decimal\Decimal;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Ekyna\Component\Commerce\Subject\Model\SubjectInterface;
use Ekyna\Component\Commerce\Supplier\Model\SupplierInterface;
use Ekyna\Component\Commerce\Supplier\Model\SupplierProductInterface;
use Ekyna\Component\Commerce\Supplier\Repository\SupplierProductRepositoryInterface;
use Ekyna\Component\Resource\Doctrine\ORM\Repository\ResourceRepository;

/**
 * Class SupplierProductRepository
 * @package Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Repository
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SupplierProductRepository extends ResourceRepository implements SupplierProductRepositoryInterface
{
    private ?Query $findBySubjectQuery                = null;
    private ?Query $findLatestWithPriceBySubjectQuery = null;
    private ?Query $getAvailableSumBySubjectQuery     = null;
    private ?Query $getOrderedSumBySubjectQuery       = null;
    private ?Query $getMinEdaBySubjectQuery           = null;
    private ?Query $findBySubjectAndSupplierQuery     = null;

    public function existsForSupplier(SupplierInterface $supplier): bool
    {
        $qb = $this->createQueryBuilder();

        return null !== $qb
                ->select('sp.id')
                ->andWhere($qb->expr()->eq('sp.supplier', ':supplier'))
                ->setMaxResults(1)
                ->getQuery()
                ->useQueryCache(true)
                ->setParameter('supplier', $supplier)
                ->getOneOrNullResult();
    }

    public function findBySupplier(SupplierInterface $supplier): array
    {
        return $this->findBy(['supplier' => $supplier]);
    }

    public function findBySubject(SubjectInterface $subject): array
    {
        return $this
            ->getFindBySubjectQuery()
            ->setParameters([
                'provider'   => $subject::getProviderName(),
                'identifier' => $subject->getId(),
            ])
            ->getResult();
    }

    public function findLatestWithPriceBySubject(SubjectInterface $subject): ?SupplierProductInterface
    {
        return $this
            ->getFindLatestWithPriceBySubjectQuery()
            ->setParameters([
                'provider'   => $subject::getProviderName(),
                'identifier' => $subject->getId(),
            ])
            ->getOneOrNullResult();
    }

    public function getMinEstimatedDateOfArrivalBySubject(SubjectInterface $subject): ?DateTimeInterface
    {
        // TODO Greater than today ?
        $result = $this
            ->getGetMinEdaBySubjectQuery()
            ->setParameters([
                'provider'   => $subject::getProviderName(),
                'identifier' => $subject->getId(),
            ])
            ->getSingleScalarResult();

        return null !== $result ? new DateTime($result) : null;
    }

    public function getAvailableQuantitySumBySubject(SubjectInterface $subject): Decimal
    {
        $total = $this
            ->getGetAvailableSumBySubjectQuery()
            ->setParameters([
                'provider'   => $subject::getProviderName(),
                'identifier' => $subject->getId(),
            ])
            ->getSingleScalarResult();

        return new Decimal($total ?: 0);
    }

    public function getOrderedQuantitySumBySubject(SubjectInterface $subject): Decimal
    {
        $total = $this
            ->getGetOrderedSumBySubjectQuery()
            ->setParameters([
                'provider'   => $subject::getProviderName(),
                'identifier' => $subject->getId(),
            ])
            ->getSingleScalarResult();

        return new Decimal($total ?: 0);
    }

    public function findOneBySubjectAndSupplier(
        SubjectInterface         $subject,
        SupplierInterface        $supplier,
        SupplierProductInterface $exclude = null
    ): ?SupplierProductInterface {
        $parameters = [
            'provider'   => $subject::getProviderName(),
            'identifier' => $subject->getId(),
            'supplier'   => $supplier,
        ];

        if (null === $exclude) {
            return $this
                ->getFindBySubjectAndSupplierQuery()
                ->setParameters($parameters)
                ->getOneOrNullResult();
        }

        $as = $this->getAlias();
        $qb = $this->createFindBySubjectQueryBuilder();

        $parameters['exclude'] = $exclude->getId();

        return $qb
            ->andWhere($qb->expr()->eq($as . '.supplier', ':supplier'))
            ->andWhere($qb->expr()->neq($as . '.id', ':exclude'))
            ->getQuery()
            ->setParameters($parameters)
            ->getOneOrNullResult();
    }

    /**
     * Returns the "find by subject" query.
     */
    protected function getFindBySubjectQuery(): Query
    {
        if (null !== $this->findBySubjectQuery) {
            return $this->findBySubjectQuery;
        }

        $qb = $this->createFindBySubjectQueryBuilder();

        return $this->findBySubjectQuery = $qb->getQuery();
    }

    /**
     * Returns the "find latest with price by subject" query.
     */
    protected function getFindLatestWithPriceBySubjectQuery(): Query
    {
        if (null !== $this->findLatestWithPriceBySubjectQuery) {
            return $this->findLatestWithPriceBySubjectQuery;
        }

        $qb = $this->createFindLatestWithPriceBySubjectQB();

        return $this->findLatestWithPriceBySubjectQuery = $qb->getQuery();
    }

    /**
     * Returns the "get available quantity sum by subject" query.
     */
    protected function getGetAvailableSumBySubjectQuery(): Query
    {
        if (null !== $this->getAvailableSumBySubjectQuery) {
            return $this->getAvailableSumBySubjectQuery;
        }

        $as = $this->getAlias();
        $qb = $this->createFindBySubjectQueryBuilder();

        $qb
            ->andWhere($qb->expr()->gte($as . '.availableStock', 0))
            ->select('SUM(' . $as . '.availableStock) as available');

        return $this->getAvailableSumBySubjectQuery = $qb->getQuery();
    }

    /**
     * Returns the "get ordered quantity sum by subject" query.
     */
    protected function getGetOrderedSumBySubjectQuery(): Query
    {
        if (null !== $this->getOrderedSumBySubjectQuery) {
            return $this->getOrderedSumBySubjectQuery;
        }

        $as = $this->getAlias();
        $qb = $this->createFindBySubjectQueryBuilder();

        $qb
            ->andWhere($qb->expr()->gte($as . '.orderedStock', 0))
            ->select('SUM(' . $as . '.orderedStock) as ordered');

        return $this->getOrderedSumBySubjectQuery = $qb->getQuery();
    }

    /**
     * Returns the "get estimated date of arrival by subject" query.
     */
    protected function getGetMinEdaBySubjectQuery(): Query
    {
        if (null !== $this->getMinEdaBySubjectQuery) {
            return $this->getMinEdaBySubjectQuery;
        }

        $as = $this->getAlias();
        $qb = $this->createFindBySubjectQueryBuilder();

        $qb
            ->andWhere($qb->expr()->isNotNull($as . '.estimatedDateOfArrival'))
            ->andWhere(
                $qb->expr()->orX(
                    $qb->expr()->gte($as . '.orderedStock', 0),
                    $qb->expr()->gte($as . '.availableStock', 0)
                )
            )
            ->select('MIN(' . $as . '.estimatedDateOfArrival) as eda');

        return $this->getMinEdaBySubjectQuery = $qb->getQuery();
    }

    /**
     * Returns the "find by subject and supplier" query.
     */
    protected function getFindBySubjectAndSupplierQuery(): Query
    {
        if (null !== $this->findBySubjectAndSupplierQuery) {
            return $this->findBySubjectAndSupplierQuery;
        }

        $qb = $this->createFindBySubjectQueryBuilder();
        $as = $this->getAlias();

        return $this->findBySubjectAndSupplierQuery = $qb
            ->andWhere($qb->expr()->eq($as . '.supplier', ':supplier'))
            ->getQuery();
    }

    /**
     * Creates a "find by subject" query builder.
     */
    private function createFindBySubjectQueryBuilder(): QueryBuilder
    {
        $as = $this->getAlias();
        $qb = $this->createQueryBuilder();

        return $qb
            ->andWhere($qb->expr()->eq($as . '.subjectIdentity.provider', ':provider'))
            ->andWhere($qb->expr()->eq($as . '.subjectIdentity.identifier', ':identifier'));
    }

    /**
     * Creates a "find latest with price by subject" query builder.
     */
    private function createFindLatestWithPriceBySubjectQB(): QueryBuilder
    {
        $as = $this->getAlias();
        $qb = $this->createQueryBuilder();

        return $qb
            ->andWhere($qb->expr()->eq($as . '.subjectIdentity.provider', ':provider'))
            ->andWhere($qb->expr()->eq($as . '.subjectIdentity.identifier', ':identifier'))
            ->andWhere($qb->expr()->neq($as . '.netPrice', 0))
            ->addOrderBy($as . '.createdAt', 'DESC')
            ->setMaxResults(1);
    }

    protected function getAlias(): string
    {
        return 'sp';
    }
}
