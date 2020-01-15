<?php

namespace Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Repository;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Ekyna\Component\Commerce\Customer\Model\CustomerInterface;
use Ekyna\Component\Commerce\Order\Model\OrderInvoiceInterface;
use Ekyna\Component\Commerce\Order\Model\OrderStates;
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
     * @var Query
     */
    private $customerInvoiceSumQuery;

    /**
     * @var Query
     */
    private $customerCreditSumQuery;

    /**
     * @var Query
     */
    private $customerInvoiceCountQuery;

    /**
     * @var Query
     */
    private $customerCreditCountQuery;


    /**
     * @inheritdoc
     *
     * @return OrderInvoiceInterface[]
     */
    public function findByCustomer(CustomerInterface $customer, $limit = null): array
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
            ->setParameters([
                'customer' => $customer,
                'sample'   => false,
            ])
            ->getResult();
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
        $qb = $this->createQueryBuilder('i');

        $ex = $qb->expr();
        $qb
            ->join('i.order', 'o')
            ->andWhere($ex->eq('o.customer', ':customer'))
            ->andWhere($ex->eq('o.sample', ':sample'))
            ->addOrderBy('i.createdAt', 'ASC');

        if ($currency) {
            $qb
                ->join('o.currency', 'c')
                ->andWhere($ex->eq('c.code', ':currency'));
        }

        if ($from && $to) {
            $qb->andWhere($ex->between('i.createdAt', ':from', ':to'));
        } elseif ($from) {
            $qb->andWhere($ex->gte('i.createdAt', ':from'));
        } elseif ($to) {
            $qb->andWhere($ex->lte('i.createdAt', ':to'));
        }

        $query = $qb
            ->getQuery()
            ->setParameters([
                'customer' => $customer,
                'sample'   => false,
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
     *
     * @return OrderInvoiceInterface
     */
    public function findOneByCustomerAndNumber(CustomerInterface $customer, $number): ?OrderInvoiceInterface
    {
        $qb = $this->createQueryBuilder('i');

        return $qb
            ->join('i.order', 'o')
            ->andWhere($qb->expr()->eq('o.customer', ':customer'))
            ->andWhere($qb->expr()->eq('o.sample', ':sample'))
            ->andWhere($qb->expr()->eq('i.number', ':number'))
            ->addOrderBy('i.createdAt', 'ASC')
            ->getQuery()
            ->setParameters([
                'customer' => $customer,
                'sample'   => false,
                'number'   => $number,
            ])
            ->getOneOrNullResult();
    }

    /**
     * @inheritDoc
     */
    public function findByMonth(\DateTime $date): array
    {
        $qb = $this->createQueryBuilder('i');

        $start = clone $date;
        $start->modify('first day of this month');
        $start->setTime(0, 0, 0, 0);

        $end = clone $date;
        $end->modify('last day of this month');
        $end->setTime(23, 59, 59, 999999);

        return $qb
            ->join('i.order', 'o')
            ->andWhere($qb->expr()->between('i.createdAt', ':start', ':end'))
            ->andWhere($qb->expr()->eq('o.sample', ':sample'))
            ->addOrderBy('i.createdAt', 'ASC')
            ->getQuery()
            ->setParameter('start', $start, Types::DATETIME_MUTABLE)
            ->setParameter('end', $end, Types::DATETIME_MUTABLE)
            ->setParameter('sample', false)
            ->getResult();
    }

    /**
     * @inheritDoc
     */
    public function findDueInvoices(CustomerInterface $customer = null, string $currency = null): array
    {
        return $this
            ->getDueInvoicesQueryBuilder($customer, $currency)
            ->addOrderBy('i.dueDate', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @inheritDoc
     */
    public function getDueTotal(CustomerInterface $customer = null): float
    {
        return (float)$this
            ->getDueInvoicesQueryBuilder($customer)
            ->select('SUM(i.grandTotal - i.paidTotal)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * @inheritDoc
     */
    public function findFallInvoices(CustomerInterface $customer = null, string $currency = null): array
    {
        return $this
            ->getFallInvoicesQueryBuilder($customer, $currency)
            ->addOrderBy('i.dueDate', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @inheritDoc
     */
    public function getFallTotal(CustomerInterface $customer = null): float
    {
        return (float)$this
            ->getFallInvoicesQueryBuilder($customer)
            ->select('SUM(i.grandTotal - i.paidTotal)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * @inheritDoc
     */
    public function getCustomerInvoiceSum(CustomerInterface $customer, \DateTime $from, \DateTime $to): float
    {
        return (float)$this
            ->getCustomerInvoiceSumQuery()
            ->setParameter('customer', $customer)
            ->setParameter('from', $from, Types::DATETIME_MUTABLE)
            ->setParameter('to', $to, Types::DATETIME_MUTABLE)
            ->getSingleScalarResult();
    }

    /**
     * @inheritDoc
     */
    public function getCustomerCreditSum(CustomerInterface $customer, \DateTime $from, \DateTime $to): float
    {
        return (float)$this
            ->getCustomerCreditSumQuery()
            ->setParameter('customer', $customer)
            ->setParameter('from', $from, Types::DATETIME_MUTABLE)
            ->setParameter('to', $to, Types::DATETIME_MUTABLE)
            ->getSingleScalarResult();
    }

    /**
     * @inheritDoc
     */
    public function getCustomerInvoiceCount(CustomerInterface $customer, \DateTime $from, \DateTime $to): int
    {
        return (int)$this
            ->getCustomerInvoiceCountQuery()
            ->setParameter('customer', $customer)
            ->setParameter('from', $from, Types::DATETIME_MUTABLE)
            ->setParameter('to', $to, Types::DATETIME_MUTABLE)
            ->getSingleScalarResult();
    }

    /**
     * @inheritDoc
     */
    public function getCustomerCreditCount(CustomerInterface $customer, \DateTime $from, \DateTime $to): int
    {
        return (int)$this
            ->getCustomerCreditCountQuery()
            ->setParameter('customer', $customer)
            ->setParameter('from', $from, Types::DATETIME_MUTABLE)
            ->setParameter('to', $to, Types::DATETIME_MUTABLE)
            ->getSingleScalarResult();
    }

    /**
     * Returns the due invoices query builder.
     *
     * @param CustomerInterface $customer
     * @param string            $currency
     *
     * @return QueryBuilder
     */
    protected function getDueInvoicesQueryBuilder(
        CustomerInterface $customer = null,
        string $currency = null
    ): QueryBuilder {
        $qb = $this->createQueryBuilder('i');

        $qb
            ->join('i.order', 'o')
            ->andWhere($qb->expr()->eq('i.credit', ':credit'))
            ->andWhere($qb->expr()->lt('i.paidTotal', 'i.grandTotal'))
            ->andWhere($qb->expr()->isNotNull('i.dueDate'))
            ->andWhere($qb->expr()->lte('i.dueDate', ':today'))
            ->andWhere($qb->expr()->notIn('o.state', ':states'))
            ->andWhere($qb->expr()->eq('o.sample', ':sample'));

        $this->addInvoicesQBParameters($qb, $customer, $currency);

        return $qb;
    }

    /**
     * Returns the fall invoices query builder.
     *
     * @param CustomerInterface $customer
     * @param string            $currency
     *
     * @return QueryBuilder
     */
    protected function getFallInvoicesQueryBuilder(
        CustomerInterface $customer = null,
        string $currency = null
    ): QueryBuilder {
        $qb = $this->createQueryBuilder('i');

        $qb
            ->join('i.order', 'o')
            ->andWhere($qb->expr()->eq('i.credit', ':credit'))
            ->andWhere($qb->expr()->lt('i.paidTotal', 'i.grandTotal'))
            ->andWhere($qb->expr()->isNotNull('i.dueDate'))
            ->andWhere($qb->expr()->gt('i.dueDate', ':today'))
            ->andWhere($qb->expr()->notIn('o.state', ':states'))
            ->andWhere($qb->expr()->eq('o.sample', ':sample'));

        $this->addInvoicesQBParameters($qb, $customer, $currency);

        return $qb;
    }

    /**
     * Adds the due/fall invoices query builder parameters.
     *
     * @param QueryBuilder           $qb
     * @param CustomerInterface|null $customer
     * @param string|null            $currency
     *
     * @throws \Exception
     */
    protected function addInvoicesQBParameters(
        QueryBuilder $qb,
        CustomerInterface $customer = null,
        string $currency = null
    ): void {
        $today = new \DateTime();
        $today->setTime(23, 59, 59, 999999);

        $qb
            ->setParameter('credit', false)
            ->setParameter('today', $today, Types::DATETIME_MUTABLE)
            ->setParameter('states', [OrderStates::STATE_CANCELED, OrderStates::STATE_REFUNDED])
            ->setParameter('sample', false);

        if ($customer) {
            $qb
                ->andWhere($qb->expr()->eq('o.customer', ':customer'))
                ->setParameter('customer', $customer);
        }

        if ($currency) {
            $qb
                ->join('o.currency', 'c')
                ->andWhere($qb->expr()->eq('c.code', ':currency'))
                ->setParameter('currency', $currency);
        }
    }

    /**
     * Returns the "customer invoice sum query".
     *
     * @return Query
     */
    protected function getCustomerInvoiceSumQuery(): Query
    {
        if ($this->customerInvoiceSumQuery) {
            return $this->customerInvoiceSumQuery;
        }

        $qb = $this->createQueryBuilder('i');
        $ex = $qb->expr();

        return $this->customerInvoiceSumQuery = $qb
            ->select('SUM(i.grandTotal)')
            ->join('i.order', 'o')
            ->andWhere($ex->eq('i.credit', ':credit'))
            ->andWhere($ex->eq('o.sample', ':sample'))
            ->andWhere($ex->eq('o.customer', ':customer'))
            ->andWhere($ex->between('i.createdAt', ':from', ':to'))
            ->addGroupBy('o.customer')
            ->getQuery()
            ->setParameters([
                'sample' => false,
                'credit' => false,
            ])
            ->useQueryCache(true);
    }

    /**
     * Returns the "customer credit sum" query.
     *
     * @return Query
     */
    protected function getCustomerCreditSumQuery(): Query
    {
        if ($this->customerCreditSumQuery) {
            return $this->customerCreditSumQuery;
        }

        $qb = $this->createQueryBuilder('i');
        $ex = $qb->expr();

        return $this->customerCreditSumQuery = $qb
            ->select('SUM(i.grandTotal)')
            ->join('i.order', 'o')
            ->andWhere($ex->eq('i.credit', ':credit'))
            ->andWhere($ex->eq('o.sample', ':sample'))
            ->andWhere($ex->eq('o.customer', ':customer'))
            ->andWhere($ex->between('i.createdAt', ':from', ':to'))
            ->addGroupBy('o.customer')
            ->getQuery()
            ->setParameters([
                'credit' => true,
                'sample' => false,
            ])
            ->useQueryCache(true);
    }

    /**
     * Returns the "customer invoice count" query.
     *
     * @return Query
     */
    protected function getCustomerInvoiceCountQuery(): Query
    {
        if ($this->customerInvoiceCountQuery) {
            return $this->customerInvoiceCountQuery;
        }

        $qb = $this->createQueryBuilder('i');
        $ex = $qb->expr();

        return $this->customerInvoiceCountQuery = $qb
            ->select('COUNT(p.id)')
            ->join('i.order', 'o')
            ->andWhere($ex->eq('i.credit', ':credit'))
            ->andWhere($ex->eq('o.sample', ':sample'))
            ->andWhere($ex->eq('o.customer', ':customer'))
            ->andWhere($ex->between('i.createdAt', ':from', ':to'))
            ->addGroupBy('o.customer')
            ->getQuery()
            ->setParameters([
                'credit' => false,
                'sample' => false,
            ])
            ->useQueryCache(true);
    }

    /**
     * Returns the "customer credit count" query.
     *
     * @return Query
     */
    protected function getCustomerCreditCountQuery(): Query
    {
        if ($this->customerCreditCountQuery) {
            return $this->customerCreditCountQuery;
        }

        $qb = $this->createQueryBuilder('i');
        $ex = $qb->expr();

        return $this->customerCreditCountQuery = $qb
            ->select('COUNT(i.id)')
            ->join('i.order', 'o')
            ->andWhere($ex->eq('i.credit', ':credit'))
            ->andWhere($ex->eq('o.sample', ':sample'))
            ->andWhere($ex->eq('o.customer', ':customer'))
            ->andWhere($ex->between('i.createdAt', ':from', ':to'))
            ->addGroupBy('o.customer')
            ->getQuery()
            ->setParameters([
                'credit' => true,
                'sample' => false,
            ])
            ->useQueryCache(true);
    }
}
