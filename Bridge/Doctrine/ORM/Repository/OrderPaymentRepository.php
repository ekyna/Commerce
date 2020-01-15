<?php

namespace Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Repository;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Query;
use Ekyna\Component\Commerce\Bridge\Payum\OutstandingBalance\Constants as Outstanding;
use Ekyna\Component\Commerce\Customer\Model\CustomerInterface;
use Ekyna\Component\Commerce\Exception\RuntimeException;
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
     * @return void
     */
    public function createNew()
    {
        throw new RuntimeException("Disabled: use payment factory.");
    }

    /**
     * @inheritDoc
     */
    public function findByCustomerAndDateRange(
        CustomerInterface $customer,
        string $currency = null,
        \DateTime $from = null,
        \DateTime $to = null
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

    /**
     * @inheritDoc
     */
    public function getCustomerPaymentSum(CustomerInterface $customer, \DateTime $from, \DateTime $to): float
    {
        return (float)$this
            ->getCustomerPaymentSumQuery()
            ->setParameter('customer', $customer)
            ->setParameter('from', $from, Types::DATETIME_MUTABLE)
            ->setParameter('to', $to, Types::DATETIME_MUTABLE)
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
            ->setParameter('from', $from, Types::DATETIME_MUTABLE)
            ->setParameter('to', $to, Types::DATETIME_MUTABLE)
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
            ->setParameter('from', $from, Types::DATETIME_MUTABLE)
            ->setParameter('to', $to, Types::DATETIME_MUTABLE)
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
            ->setParameter('from', $from, Types::DATETIME_MUTABLE)
            ->setParameter('to', $to, Types::DATETIME_MUTABLE)
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

        return $this->customerPaymentSumQuery = $this->createCustomerSumQuery(PaymentStates::STATE_CAPTURED);
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

        return $this->customerRefundSumQuery = $this->createCustomerSumQuery(PaymentStates::STATE_REFUNDED);
    }

    /**
     * Creates the customer payment sum query builder.
     *
     * @param string $paymentState
     *
     * @return Query
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
     *
     * @return Query
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
     *
     * @return Query
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
     *
     * @param string $paymentState
     *
     * @return Query
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
