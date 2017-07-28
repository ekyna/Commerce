<?php

namespace Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Repository;

use Ekyna\Component\Commerce\Subject\Model\SubjectInterface;
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
     * @inheritDoc
     */
    public function findBySubject(SubjectInterface $subject)
    {
        return $this
            ->getFindBySubjectQuery()
            ->setParameters([
                'provider' => $subject->getProviderName(),
                'identifier' => $subject->getId(),
            ])
            ->getResult();
    }

    /**
     * Returns the find by subject query.
     *
     * @return \Doctrine\ORM\Query
     */
    public function getFindBySubjectQuery()
    {
        if (null !== $this->findBySubjectQuery) {
            return $this->findBySubjectQuery;
        }

        $qb = $this->createQueryBuilder('sp');

        return $this->findBySubjectQuery = $qb
            ->andWhere($qb->expr()->eq('sp.subjectIdentity.provider', ':provider'))
            ->andWhere($qb->expr()->eq('sp.subjectIdentity.identifier', ':identifier'))
            ->getQuery();
    }
}
