<?php

namespace Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Repository;

use Ekyna\Component\Commerce\Customer\Model\CustomerInterface;
use Ekyna\Component\Commerce\Order\Model\OrderInvoiceInterface;
use Ekyna\Component\Commerce\Order\Repository\OrderInvoiceRepositoryInterface;
use Ekyna\Component\Resource\Doctrine\ORM\ResourceRepository;

/**
 * Class OrderInvoiceRepository
 * @package Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Repository
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class OrderInvoiceRepository extends ResourceRepository implements OrderInvoiceRepositoryInterface
{
    /**
     * @inheritdoc
     *
     * @return OrderInvoiceInterface[]
     */
    public function findByCustomer(CustomerInterface $customer, $limit = null)
    {
        $qb = $this->createQueryBuilder('i');

        $qb
            ->join('i.order', 'o')
            ->andWhere($qb->expr()->eq('o.customer', ':customer'))
            ->addOrderBy('i.createdAt', 'ASC');

        if (0 < $limit) {
            $qb->setMaxResults($limit);
        }

        return $qb
            ->getQuery()
            ->setParameter('customer', $customer)
            ->getResult();
    }

    /**
     * @inheritDoc
     *
     * @return OrderInvoiceInterface
     */
    public function findOneByCustomerAndNumber(CustomerInterface $customer, $number)
    {
        $qb = $this->createQueryBuilder('i');

        return $qb
            ->join('i.order', 'o')
            ->andWhere($qb->expr()->eq('o.customer', ':customer'))
            ->andWhere($qb->expr()->eq('i.number', ':number'))
            ->addOrderBy('i.createdAt', 'ASC')
            ->getQuery()
            ->setParameter('customer', $customer)
            ->setParameter('number', $number)
            ->getOneOrNullResult();
    }
}