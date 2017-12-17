<?php

namespace Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Repository;

use Ekyna\Component\Commerce\Subject\Model\SubjectInterface;
use Ekyna\Component\Commerce\Supplier\Repository\SupplierOrderItemRepositoryInterface;
use Ekyna\Component\Resource\Doctrine\ORM\ResourceRepository;

/**
 * Class SupplierOrderItemRepository
 * @package Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Repository
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SupplierOrderItemRepository extends ResourceRepository implements SupplierOrderItemRepositoryInterface
{
    /**
     * @inheritdoc
     */
    public function findLatestOrderedBySubject(SubjectInterface $subject)
    {
        $qb = $this->createQueryBuilder('i');

        return $qb
            ->join('i.supplierOrder', 'o')
            ->join('i.product', 'p')
            ->andWhere($qb->expr()->isNotNull('i.netPrice'))
            ->andWhere($qb->expr()->eq('p.subjectIdentity.provider', ':provider'))
            ->andWhere($qb->expr()->eq('p.subjectIdentity.identifier', ':identifier'))
            ->orderBy('o.createdAt', 'DESC')
            ->getQuery()
            ->setParameters([
                'provider'   => $subject->getProviderName(),
                'identifier' => $subject->getId(),
            ])
            ->getOneOrNullResult();
    }
}
