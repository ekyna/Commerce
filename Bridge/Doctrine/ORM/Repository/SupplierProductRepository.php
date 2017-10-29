<?php

namespace Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Repository;

use Ekyna\Component\Commerce\Subject\Model\SubjectInterface;
use Ekyna\Component\Commerce\Supplier\Model\SupplierInterface;
use Ekyna\Component\Commerce\Supplier\Model\SupplierProductInterface;
use Ekyna\Component\Commerce\Supplier\Repository\SupplierProductRepositoryInterface;
use Ekyna\Component\Resource\Doctrine\ORM\ResourceRepository;

/**
 * Class SupplierProductRepository
 * @package Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Repository
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SupplierProductRepository extends ResourceRepository implements SupplierProductRepositoryInterface
{
    /**
     * @var \Doctrine\ORM\Query
     */
    private $findBySubjectQuery;

    /**
     * @var \Doctrine\ORM\Query
     */
    private $getAvailableSumBySubjectQuery;

    /**
     * @var \Doctrine\ORM\Query
     */
    private $getMinEdaBySubjectQuery;

    /**
     * @var \Doctrine\ORM\Query
     */
    private $findBySubjectAndSupplierQuery;


    /**
     * @inheritDoc
     */
    public function findBySupplier(SupplierInterface $supplier)
    {
        return $this->findBy(['supplier' => $supplier]);
    }

    /**
     * @inheritDoc
     */
    public function findBySubject(SubjectInterface $subject)
    {
        return $this
            ->getFindBySubjectQuery()
            ->setParameters([
                'provider'   => $subject->getProviderName(),
                'identifier' => $subject->getId(),
            ])
            ->getResult();
    }

    /**
     * @inheritDoc
     */
    public function getMinEstimatedDateOfArrivalBySubject(SubjectInterface $subject)
    {
        $result = $this
            ->getGetMinEdaBySubjectQuery()
            ->setParameters([
                'provider'   => $subject->getProviderName(),
                'identifier' => $subject->getId(),
            ])
            ->getSingleScalarResult();

        return null !== $result ? new \DateTime($result) : null;
    }

    /**
     * @inheritDoc
     */
    public function getAvailableQuantitySumBySubject(SubjectInterface $subject)
    {
        return (float)$this
            ->getGetAvailableSumBySubjectQuery()
            ->setParameters([
                'provider'   => $subject->getProviderName(),
                'identifier' => $subject->getId(),
            ])
            ->getSingleScalarResult();
    }

    /**
     * @inheritDoc
     */
    public function findBySubjectAndSupplier(
        SubjectInterface $subject,
        SupplierInterface $supplier,
        SupplierProductInterface $exclude = null
    ) {
        $parameters = [
            'provider'   => $subject->getProviderName(),
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
     *
     * @return \Doctrine\ORM\Query
     */
    protected function getFindBySubjectQuery()
    {
        if (null !== $this->findBySubjectQuery) {
            return $this->findBySubjectQuery;
        }

        $qb = $this->createFindBySubjectQueryBuilder();

        return $this->findBySubjectQuery = $qb->getQuery();
    }

    /**
     * Returns the "get available quantity sum by subject" query.
     *
     * @return \Doctrine\ORM\Query
     */
    protected function getGetAvailableSumBySubjectQuery()
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
     * Returns the "get estimated date of arrival by subject" query.
     *
     * @return \Doctrine\ORM\Query
     */
    protected function getGetMinEdaBySubjectQuery()
    {
        if (null !== $this->getMinEdaBySubjectQuery) {
            return $this->getMinEdaBySubjectQuery;
        }

        $as = $this->getAlias();
        $qb = $this->createFindBySubjectQueryBuilder();

        $qb
            ->andWhere($qb->expr()->isNotNull($as . '.estimatedDateOfArrival'))
            ->andWhere($qb->expr()->orX(
                $qb->expr()->gte($as . '.orderedStock', 0),
                $qb->expr()->gte($as . '.availableStock', 0)
            ))
            ->select('MIN(' . $as . '.estimatedDateOfArrival) as eda');

        return $this->getMinEdaBySubjectQuery = $qb->getQuery();
    }

    /**
     * Returns the "find by subject and supplier" query.
     *
     * @return \Doctrine\ORM\Query
     */
    protected function getFindBySubjectAndSupplierQuery()
    {
        if (null !== $this->findBySubjectAndSupplierQuery) {
            return $this->findBySubjectAndSupplierQuery;
        }

        $qb = $this->createFindBySubjectQueryBuilder();

        return $this->findBySubjectAndSupplierQuery = $qb
            ->andWhere($qb->expr()->eq($this->getAlias() . '.supplier', ':supplier'))
            ->getQuery();
    }

    /**
     * Creates a "find by subject" query builder.
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    private function createFindBySubjectQueryBuilder()
    {
        $as = $this->getAlias();
        $qb = $this->createQueryBuilder();

        return $qb
            ->andWhere($qb->expr()->eq($as . '.subjectIdentity.provider', ':provider'))
            ->andWhere($qb->expr()->eq($as . '.subjectIdentity.identifier', ':identifier'));
    }

    /**
     * @inheritDoc
     */
    protected function getAlias()
    {
        return 'sp';
    }
}
