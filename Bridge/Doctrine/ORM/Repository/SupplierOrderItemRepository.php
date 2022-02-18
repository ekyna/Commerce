<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Repository;

use Ekyna\Component\Commerce\Subject\Model\SubjectInterface;
use Ekyna\Component\Commerce\Supplier\Model\SupplierOrderItemInterface;
use Ekyna\Component\Commerce\Supplier\Repository\SupplierOrderItemRepositoryInterface;
use Ekyna\Component\Resource\Doctrine\ORM\Repository\ResourceRepository;

/**
 * Class SupplierOrderItemRepository
 * @package Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Repository
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SupplierOrderItemRepository extends ResourceRepository implements SupplierOrderItemRepositoryInterface
{
    public function findLatestOrderedBySubject(SubjectInterface $subject): ?SupplierOrderItemInterface
    {
        $qb = $this->createQueryBuilder('i');

        return $qb
            ->join('i.order', 'o')
            ->join('i.product', 'p')
            ->andWhere($qb->expr()->isNotNull('i.netPrice'))
            ->andWhere($qb->expr()->eq('p.subjectIdentity.provider', ':provider'))
            ->andWhere($qb->expr()->eq('p.subjectIdentity.identifier', ':identifier'))
            ->orderBy('o.createdAt', 'DESC')
            ->getQuery()
            ->setMaxResults(1)
            ->setParameters([
                'provider'   => $subject::getProviderName(),
                'identifier' => $subject->getId(),
            ])
            ->getOneOrNullResult();
    }

    public function findPaidAndNotDelivered(): array
    {
        $qb = $this->createQueryBuilder('i');

        return $qb
            ->join('i.order', 'o')
            ->join('i.product', 'p')
            ->leftJoin('i.deliveryItems', 'di')
            ->andWhere($qb->expr()->isNotNull('o.paymentDate'))
            ->andWhere($qb->expr()->isNull('di'))
            ->getQuery()
            ->getResult();
    }
}
