<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Repository;

use DateTime;
use DateTimeInterface;
use Decimal\Decimal;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Query;
use Ekyna\Component\Commerce\Bridge\Payum\OutstandingBalance\Constants as Outstanding;
use Ekyna\Component\Commerce\Customer\Model\CustomerInterface;
use Ekyna\Component\Commerce\Order\Model\OrderInterface;
use Ekyna\Component\Commerce\Order\Model\OrderPaymentInterface;
use Ekyna\Component\Commerce\Order\Repository\OrderPaymentRepositoryInterface;
use Ekyna\Component\Commerce\Payment\Model\PaymentStates;
use Ekyna\Component\Commerce\Payment\Repository\PaymentRepositoryInterface;

/**
 * Class OrderPaymentRepository
 * @package Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Repository
 * @author  Etienne Dauvergne <contact@ekyna.com>
 *
 * @implements PaymentRepositoryInterface<OrderPaymentInterface>
 */
class OrderPaymentRepository extends AbstractPaymentRepository implements OrderPaymentRepositoryInterface
{
    private ?Query $customerPaymentSumQuery   = null;
    private ?Query $customerRefundSumQuery    = null;
    private ?Query $customerPaymentCountQuery = null;
    private ?Query $customerRefundCountQuery  = null;

    public function findOneByOrderAndKey(OrderInterface $order, string $key): ?OrderPaymentInterface
    {
        return $this->findOneBy([
            'order' => $order,
            'key'   => $key,
        ]);
    }

    public function findByCustomerAndDateRange(
        CustomerInterface $customer,
        string $currency = null,
        DateTimeInterface $from = null,
        DateTimeInterface $to = null
    ): array {
        $qb = $this->createQueryBuilder('p');

        $ex = $qb->expr();
        $qb
            ->join('p.order', 'o')
            ->join('p.method', 'm')
            ->andWhere($ex->eq('o.customer', ':customer'))
            ->andWhere($ex->eq('o.sample', ':sample'))
            ->andWhere($ex->in('p.state', ':states'))
            ->andWhere($ex->neq('m.factoryName', ':factory'))
            ->addOrderBy('p.completedAt', 'ASC');

        if ($currency) {
            $qb
                ->join('o.currency', 'c')
                ->andWhere($ex->eq('c.code', ':currency'));
        }

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
                'states'   => PaymentStates::getPaidStates(true),
                'factory'  => Outstanding::FACTORY_NAME,
            ]);

        if ($currency) {
            $query->setParameter('currency', $currency);
        }
        if ($from) {
            $query->setParameter('from', $from, Types::DATETIME_MUTABLE);
        }
        if ($to) {
            $query->setParameter('to', $to, Types::DATETIME_MUTABLE);
        }

        return $query->getResult();
    }

    public function getCustomerPaymentSum(CustomerInterface $customer, DateTime $from, DateTime $to): Decimal
    {
        $total = $this
            ->getCustomerPaymentSumQuery()
            ->setParameter('customer', $customer)
            ->setParameter('from', $from, Types::DATETIME_MUTABLE)
            ->setParameter('to', $to, Types::DATETIME_MUTABLE)
            ->getSingleScalarResult();

        return new Decimal($total);
    }

    public function getCustomerRefundSum(CustomerInterface $customer, DateTime $from, DateTime $to): Decimal
    {
        $total = $this
            ->getCustomerRefundSumQuery()
            ->setParameter('customer', $customer)
            ->setParameter('from', $from, Types::DATETIME_MUTABLE)
            ->setParameter('to', $to, Types::DATETIME_MUTABLE)
            ->getSingleScalarResult();

        return new Decimal($total);
    }

    public function getCustomerPaymentCount(CustomerInterface $customer, DateTime $from, DateTime $to): int
    {
        return (int)$this
            ->getCustomerPaymentCountQuery()
            ->setParameter('customer', $customer)
            ->setParameter('from', $from, Types::DATETIME_MUTABLE)
            ->setParameter('to', $to, Types::DATETIME_MUTABLE)
            ->getSingleScalarResult();
    }

    public function getCustomerRefundCount(CustomerInterface $customer, DateTime $from, DateTime $to): int
    {
        return (int)$this
            ->getCustomerRefundCountQuery()
            ->setParameter('customer', $customer)
            ->setParameter('from', $from, Types::DATETIME_MUTABLE)
            ->setParameter('to', $to, Types::DATETIME_MUTABLE)
            ->getSingleScalarResult();
    }

    /**
     * Returns the "customer payment sum query".
     */
    protected function getCustomerPaymentSumQuery(): Query
    {
        if ($this->customerPaymentSumQuery) {
            return $this->customerPaymentSumQuery;
        }

        return $this->customerPaymentSumQuery = $this->createCustomerSumQuery(PaymentStates::STATE_CAPTURED);
    }

    /**
     * Returns the "customer refund sum" query.
     */
    protected function getCustomerRefundSumQuery(): Query
    {
        if ($this->customerRefundSumQuery) {
            return $this->customerRefundSumQuery;
        }

        return $this->customerRefundSumQuery = $this->createCustomerSumQuery(PaymentStates::STATE_REFUNDED);
    }

    /**
     * Creates the customer payment sum query builder.
     */
    protected function createCustomerSumQuery(string $paymentState): Query
    {
        $qb = $this->createQueryBuilder('p');
        $ex = $qb->expr();

        return $qb
            ->select('SUM(p.amount)')
            ->join('p.order', 'o')
            ->join('p.method', 'm')
            ->andWhere($ex->eq('o.sample', ':sample'))
            ->andWhere($ex->eq('p.state', ':state'))
            ->andWhere($ex->neq('m.factoryName', ':factory'))
            ->andWhere($ex->neq('o.customer', ':customer'))
            ->andWhere($ex->between('p.completed', ':from', ':to'))
            ->addGroupBy('o.customer')
            ->getQuery()
            ->setParameters([
                'sample'  => false,
                'state'   => $paymentState,
                'factory' => Outstanding::FACTORY_NAME,
            ])
            ->useQueryCache(true);
    }

    /**
     * Returns the "customer payment count" query.
     */
    protected function getCustomerPaymentCountQuery(): Query
    {
        if ($this->customerPaymentCountQuery) {
            return $this->customerPaymentCountQuery;
        }

        return $this->customerPaymentCountQuery = $this->createCustomerCountQuery(PaymentStates::STATE_CAPTURED);
    }

    /**
     * Returns the "customer refund count" query.
     */
    protected function getCustomerRefundCountQuery(): Query
    {
        if ($this->customerRefundCountQuery) {
            return $this->customerRefundCountQuery;
        }

        return $this->customerRefundCountQuery = $this->createCustomerCountQuery(PaymentStates::STATE_REFUNDED);
    }

    /**
     * Creates the customer payment count query.
     */
    protected function createCustomerCountQuery(string $paymentState): Query
    {
        $qb = $this->createQueryBuilder('p');
        $ex = $qb->expr();

        return $qb
            ->select('COUNT(p.id)')
            ->join('p.order', 'o')
            ->join('p.method', 'm')
            ->andWhere($ex->eq('o.sample', ':sample'))
            ->andWhere($ex->eq('p.state', ':state'))
            ->andWhere($ex->neq('m.factoryName', ':factory'))
            ->andWhere($ex->neq('o.customer', ':customer'))
            ->andWhere($ex->between('p.completed', ':from', ':to'))
            ->addGroupBy('o.customer')
            ->getQuery()
            ->setParameters([
                'sample'  => false,
                'state'   => $paymentState,
                'factory' => Outstanding::FACTORY_NAME,
            ])
            ->useQueryCache(true);
    }
}
