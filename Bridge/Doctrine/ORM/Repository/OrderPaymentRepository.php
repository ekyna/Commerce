<?php

namespace Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Repository;

use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\Query;
use Ekyna\Component\Commerce\Bridge\Payum\OutstandingBalance\Constants as Outstanding;
use Ekyna\Component\Commerce\Customer\Model\CustomerInterface;
use Ekyna\Component\Commerce\Order\Model\OrderPaymentInterface;
use Ekyna\Component\Commerce\Order\Repository\OrderPaymentRepositoryInterface;
use Ekyna\Component\Commerce\Payment\Model\PaymentStates;

/**
 * Class OrderPaymentRepository
 * @package Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Repository
 * @author  Etienne Dauvergne <contact@ekyna.com>
 *
 * @method OrderPaymentInterface|null findOneByKey($key)
 */
class OrderPaymentRepository extends AbstractPaymentRepository implements OrderPaymentRepositoryInterface
{
    /**
     * @var Query
     */
    private $customerPaymentSumQuery;

    /**
     * @var Query
     */
    private $customerRefundSumQuery;

    /**
     * @var Query
     */
    private $customerPaymentCountQuery;

    /**
     * @var Query
     */
    private $customerRefundCountQuery;


    /**
     * @inheritDoc
     */
    public function findByCustomerAndDateRange(
        CustomerInterface $customer,
        \DateTime $from = null,
        \DateTime $to = null,
        bool $scalar = false
    ): array {
        $qb = $this->createQueryBuilder('p');

        if ($scalar) {
            $qb->select([
                'p.id',
                'p.number',
                'p.amount',
                'p.state',
                'p.completedAt',
                'o.id as orderId',
                'o.number as orderNumber',
                'o.createdAt as orderDate',
            ]);
        }

        $ex = $qb->expr();
        $qb
            ->join('p.order', 'o')
            ->join('p.method', 'm')
            ->andWhere($ex->eq('o.customer', ':customer'))
            ->andWhere($ex->eq('o.sample', ':sample'))
            ->andWhere($ex->in('p.state', ':states'))
            ->andWhere($ex->neq('m.factoryName', ':factory'))
            ->addOrderBy('p.completedAt', 'ASC');

        if ($from && $to) {
            $qb->andWhere($ex->between('p.completedAt', ':from', ':to'));
        } elseif ($from) {
            $qb->andWhere($ex->gte('p.completedAt', ':from'));
        } elseif ($to) {
            $qb->andWhere($ex->lte('p.completedAt', ':to'));
        }

        $query = $qb
            ->getQuery()
            ->setParameters([
                'customer' => $customer,
                'sample'   => false,
                'states'   => [PaymentStates::STATE_CAPTURED, PaymentStates::STATE_REFUNDED],
                'factory'  => Outstanding::FACTORY_NAME,
            ]);

        if ($from) {
            $query->setParameter('from', $from, Type::DATETIME);
        }
        if ($to) {
            $query->setParameter('to', $to, Type::DATETIME);
        }

        return $scalar ? $query->getScalarResult() : $query->getResult();
    }

    /**
     * @inheritDoc
     */
    public function getCustomerPaymentSum(CustomerInterface $customer, \DateTime $from, \DateTime $to): float
    {
        return (float)$this
            ->getCustomerPaymentSumQuery()
            ->setParameter('customer', $customer)
            ->setParameter('from', $from, Type::DATETIME)
            ->setParameter('to', $to, Type::DATETIME)
            ->getSingleScalarResult();
    }

    /**
     * @inheritDoc
     */
    public function getCustomerRefundSum(CustomerInterface $customer, \DateTime $from, \DateTime $to): float
    {
        return (float)$this
            ->getCustomerRefundSumQuery()
            ->setParameter('customer', $customer)
            ->setParameter('from', $from, Type::DATETIME)
            ->setParameter('to', $to, Type::DATETIME)
            ->getSingleScalarResult();
    }

    /**
     * @inheritDoc
     */
    public function getCustomerPaymentCount(CustomerInterface $customer, \DateTime $from, \DateTime $to): int
    {
        return (int)$this
            ->getCustomerPaymentCountQuery()
            ->setParameter('customer', $customer)
            ->setParameter('from', $from, Type::DATETIME)
            ->setParameter('to', $to, Type::DATETIME)
            ->getSingleScalarResult();
    }

    /**
     * @inheritDoc
     */
    public function getCustomerRefundCount(CustomerInterface $customer, \DateTime $from, \DateTime $to): int
    {
        return (int)$this
            ->getCustomerRefundCountQuery()
            ->setParameter('customer', $customer)
            ->setParameter('from', $from, Type::DATETIME)
            ->setParameter('to', $to, Type::DATETIME)
            ->getSingleScalarResult();
    }

    /**
     * Returns the "customer payment sum query".
     *
     * @return Query
     */
    protected function getCustomerPaymentSumQuery(): Query
    {
        if ($this->customerPaymentSumQuery) {
            return $this->customerPaymentSumQuery;
        }

        $qb = $this->createQueryBuilder('p');
        $ex = $qb->expr();

        return $this->customerPaymentSumQuery = $qb
            ->select('SUM(p.amount)')
            ->join('p.order', 'o')
            ->join('p.method', 'm')
            ->andWhere($ex->eq('o.sample', ':sample'))
            ->andWhere($ex->eq('p.state', ':state'))
            ->andWhere($ex->neq('m.factoryName', ':factory'))
            ->andWhere($ex->neq('o.customer', ':customer'))
            ->andWhere($ex->between('p.completed', ':from', ':to'))
            ->addGroupBy('o.customer')// TODO Remove ?
            ->getQuery()
            ->setParameters([
                'sample'  => false,
                'state'   => PaymentStates::STATE_CAPTURED,
                'factory' => Outstanding::FACTORY_NAME,
            ])
            ->useQueryCache(true);
    }

    /**
     * Returns the "customer refund sum" query.
     *
     * @return Query
     */
    protected function getCustomerRefundSumQuery(): Query
    {
        if ($this->customerRefundSumQuery) {
            return $this->customerRefundSumQuery;
        }

        $qb = $this->createQueryBuilder('p');
        $ex = $qb->expr();

        return $this->customerRefundSumQuery = $qb
            ->select('SUM(p.amount)')
            ->join('p.order', 'o')
            ->join('p.method', 'm')
            ->andWhere($ex->eq('o.sample', ':sample'))
            ->andWhere($ex->eq('p.state', ':state'))
            ->andWhere($ex->neq('m.factoryName', ':factory'))
            ->andWhere($ex->neq('o.customer', ':customer'))
            ->andWhere($ex->between('p.completed', ':from', ':to'))
            ->addGroupBy('o.customer')// TODO Remove ?
            ->getQuery()
            ->setParameters([
                'sample'  => false,
                'state'   => PaymentStates::STATE_REFUNDED,
                'factory' => Outstanding::FACTORY_NAME,
            ])
            ->useQueryCache(true);
    }

    /**
     * Returns the "customer payment count" query.
     *
     * @return Query
     */
    protected function getCustomerPaymentCountQuery(): Query
    {
        if ($this->customerPaymentCountQuery) {
            return $this->customerPaymentCountQuery;
        }

        $qb = $this->createQueryBuilder('p');
        $ex = $qb->expr();

        return $this->customerPaymentCountQuery = $qb
            ->select('COUNT(p.id)')
            ->join('p.order', 'o')
            ->join('p.method', 'm')
            ->andWhere($ex->eq('o.sample', ':sample'))
            ->andWhere($ex->eq('p.state', ':state'))
            ->andWhere($ex->neq('m.factoryName', ':factory'))
            ->andWhere($ex->neq('o.customer', ':customer'))
            ->andWhere($ex->between('p.completed', ':from', ':to'))
            ->addGroupBy('o.customer')// TODO Remove ?
            ->getQuery()
            ->setParameters([
                'sample'  => false,
                'state'   => PaymentStates::STATE_CAPTURED,
                'factory' => Outstanding::FACTORY_NAME,
            ])
            ->useQueryCache(true);
    }

    /**
     * Returns the "customer refund count" query.
     *
     * @return Query
     */
    protected function getCustomerRefundCountQuery(): Query
    {
        if ($this->customerRefundCountQuery) {
            return $this->customerRefundCountQuery;
        }

        $qb = $this->createQueryBuilder('p');
        $ex = $qb->expr();

        return $this->customerRefundCountQuery = $qb
            ->select('COUNT(p.id)')
            ->join('p.order', 'o')
            ->join('p.method', 'm')
            ->andWhere($ex->eq('o.sample', ':sample'))
            ->andWhere($ex->eq('p.state', ':state'))
            ->andWhere($ex->neq('m.factoryName', ':factory'))
            ->andWhere($ex->neq('o.customer', ':customer'))
            ->andWhere($ex->between('p.completed', ':from', ':to'))
            ->addGroupBy('o.customer')// TODO Remove ?
            ->getQuery()
            ->setParameters([
                'sample'  => false,
                'state'   => PaymentStates::STATE_REFUNDED,
                'factory' => Outstanding::FACTORY_NAME,
            ])
            ->useQueryCache(true);
    }
}
