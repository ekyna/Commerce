<?php

namespace Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Repository;

use Doctrine\DBAL\Types\Type;
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
            ->andWhere($qb->expr()->eq('o.sample', ':sample'))
            ->addOrderBy('i.createdAt', 'ASC');

        if (0 < $limit) {
            $qb->setMaxResults($limit);
        }

        return $qb
            ->getQuery()
            ->setParameter('customer', $customer)
            ->setParameter('sample', false)
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
            ->andWhere($qb->expr()->eq('o.sample', ':sample'))
            ->andWhere($qb->expr()->eq('i.number', ':number'))
            ->addOrderBy('i.createdAt', 'ASC')
            ->getQuery()
            ->setParameter('customer', $customer)
            ->setParameter('sample', false)
            ->setParameter('number', $number)
            ->getOneOrNullResult();
    }

    /**
     * @inheritDoc
     */
    public function findByMonth(\DateTime $date)
    {
        $qb = $this->createQueryBuilder('i');

        $start = clone $date;
        $start->modify('first day of this month');
        $start->setTime(0,0,0);

        $end = clone $date;
        $end->modify('last day of this month');
        $end->setTime(23,59,59);

        return $qb
            ->join('i.order', 'o')
            ->andWhere($qb->expr()->between('i.createdAt', ':start', ':end'))
            ->andWhere($qb->expr()->eq('o.sample', ':sample'))
            ->addOrderBy('i.createdAt', 'ASC')
            ->getQuery()
            ->setParameter('start', $start, Type::DATETIME)
            ->setParameter('end', $end, Type::DATETIME)
            ->setParameter('sample', false)
            ->getResult();
    }
}