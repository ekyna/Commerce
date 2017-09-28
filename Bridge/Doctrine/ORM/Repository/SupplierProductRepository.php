<?php

namespace Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Repository;

use Ekyna\Component\Commerce\Subject\Model\SubjectInterface;
use Ekyna\Component\Commerce\Supplier\Model\SupplierInterface;
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
    private $findBySubjectAndSupplierQuery;


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
    public function findBySubjectAndSupplier(SubjectInterface $subject, SupplierInterface $supplier)
    {
        return $this
            ->getFindBySubjectAndSupplierQuery()
            ->setParameters([
                'provider'   => $subject->getProviderName(),
                'identifier' => $subject->getId(),
                'supplier'   => $supplier,
            ])
            ->getOneOrNullResult();
    }

    /**
     * Returns the find by subject query.
     *
     * @return \Doctrine\ORM\Query
     */
    protected function getFindBySubjectQuery()
    {
        if (null !== $this->findBySubjectQuery) {
            return $this->findBySubjectQuery;
        }

        $qb = $this->getFindBySubjectQueryBuilder();

        return $this->findBySubjectQuery = $qb->getQuery();
    }

    /**
     * Returns the find by subject and supplier query.
     *
     * @return \Doctrine\ORM\Query
     */
    protected function getFindBySubjectAndSupplierQuery()
    {
        if (null !== $this->findBySubjectAndSupplierQuery) {
            return $this->findBySubjectAndSupplierQuery;
        }

        $qb = $this->getFindBySubjectQueryBuilder();

        return $this->findBySubjectAndSupplierQuery = $qb
            ->andWhere($qb->expr()->eq('sp.supplier', ':supplier'))
            ->getQuery();
    }

    /**
     * Returns the find by subject query.
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    private function getFindBySubjectQueryBuilder()
    {
        $qb = $this->createQueryBuilder('sp');

        return $qb
            ->andWhere($qb->expr()->eq('sp.subjectIdentity.provider', ':provider'))
            ->andWhere($qb->expr()->eq('sp.subjectIdentity.identifier', ':identifier'));
    }
}
