<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Repository;

use DateTime;
use DateTimeInterface;
use Decimal\Decimal;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Ekyna\Component\Commerce\Customer\Model\CustomerInterface;
use Ekyna\Component\Commerce\Invoice\Repository\InvoiceRepositoryInterface;
use Ekyna\Component\Commerce\Order\Model\OrderInvoiceInterface;
use Ekyna\Component\Commerce\Order\Model\OrderStates;
use Ekyna\Component\Commerce\Order\Repository\OrderInvoiceRepositoryInterface;
use Ekyna\Component\Resource\Doctrine\ORM\Repository\ResourceRepository;
use Ekyna\Component\Resource\Model\DateRange;
use Exception;

/**
 * Class OrderInvoiceRepository
 * @package Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Repository
 * @author  Etienne Dauvergne <contact@ekyna.com>
 *
 * @implements InvoiceRepositoryInterface<OrderInvoiceInterface>
 */
class OrderInvoiceRepository extends ResourceRepository implements OrderInvoiceRepositoryInterface
{
    private ?Query $findByCreatedAtQuery      = null;
    private ?Query $customerInvoiceSumQuery   = null;
    private ?Query $customerCreditSumQuery    = null;
    private ?Query $customerInvoiceCountQuery = null;
    private ?Query $customerCreditCountQuery  = null;

    /**
     * @inheritDoc
     */
    public function findByCustomer(CustomerInterface $customer, int $limit = null): array
    {
        $qb = $this->createQueryBuilder('i');

        $qb
            ->join('i.order', 'o')
            ->andWhere($qb->expr()->eq('o.customer', ':customer'))
            ->andWhere($qb->expr()->eq('o.sample', ':sample'))
            ->addOrderBy('i.createdAt', 'DESC');

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
        string            $currency = null,
        DateTimeInterface $from = null,
        DateTimeInterface $to = null
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
    public function findByCreatedAt(DateRange $range, int $page, int $size): array
    {
        return $this
            ->getFindByCreatedAtQuery()
            ->setParameter('from', $range->getStart(), Types::DATETIME_IMMUTABLE)
            ->setParameter('to', $range->getEnd(), Types::DATETIME_IMMUTABLE)
            ->setFirstResult($size * $page)
            ->setMaxResults($size)
            ->getResult();
    }

    /**
     * @inheritDoc
     */
    public function findByMonth(DateTimeInterface $date): array
    {
        $qb = $this->createQueryBuilder('i');

        $start = clone $date;
        $start->modify('first day of this month');
        $start->setTime(0, 0);

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
    public function findByMonthAndCountries(DateTimeInterface $date, array $codes, bool $exclude = false): array
    {
        $qb = $this->createQueryBuilder('i');

        $start = clone $date;
        $start->modify('first day of this month');
        $start->setTime(0, 0);

        $end = clone $date;
        $end->modify('last day of this month');
        $end->setTime(23, 59, 59, 999999);

        $qb
            ->select([
                'i.credit',
                'i.goodsBase',
                'i.discountBase',
                'i.shipmentBase',
                'i.taxesTotal',
                'i.grandTotal',
            ])
            ->join('i.order', 'o')
            ->andWhere($qb->expr()->between('i.createdAt', ':start', ':end'))
            ->andWhere($qb->expr()->eq('o.sample', ':sample'))
            ->addOrderBy('i.createdAt', 'ASC');

        if (!empty($codes)) {
            $qb
                ->join('o.invoiceAddress', 'a')
                ->join('a.country', 'c');

            if ($exclude) {
                $qb->andWhere($qb->expr()->notIn('c.code', ':countries'));
            } else {
                $qb->andWhere($qb->expr()->in('c.code', ':countries'));
            }
        }

        $query = $qb->getQuery()
            ->setParameter('start', $start, Types::DATETIME_MUTABLE)
            ->setParameter('end', $end, Types::DATETIME_MUTABLE)
            ->setParameter('sample', false);

        if (!empty($codes)) {
            $query->setParameter('countries', $codes);
        }

        return $query->getScalarResult();
    }

    /**
     * @inheritDoc
     */
    public function findByOrderId(int $id): array
    {
        $qb = $this->createQueryBuilder('i');

        return $qb
            ->andWhere($qb->expr()->eq('IDENTITY(i.order)', ':id'))
            ->getQuery()
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
    public function findFirstInvoiceDate(): ?DateTime
    {
        $qb = $this->createQueryBuilder('i');

        $date = $qb
            ->select('i.createdAt')
            ->addOrderBy('i.createdAt', 'ASC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult(AbstractQuery::HYDRATE_SINGLE_SCALAR);

        return $date ? new DateTime($date) : null;
    }

    /**
     * @inheritDoc
     */
    public function getDueTotal(CustomerInterface $customer = null): Decimal
    {
        $total = $this
            ->getDueInvoicesQueryBuilder($customer)
            ->select('SUM(i.grandTotal - i.paidTotal)')
            ->getQuery()
            ->getSingleScalarResult();

        return new Decimal($total ?: 0);
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
    public function getFallTotal(CustomerInterface $customer = null): Decimal
    {
        $total = $this
            ->getFallInvoicesQueryBuilder($customer)
            ->select('SUM(i.grandTotal - i.paidTotal)')
            ->getQuery()
            ->getSingleScalarResult();

        return new Decimal($total ?: 0);
    }

    /**
     * @inheritDoc
     */
    public function getCustomerInvoiceSum(CustomerInterface $customer, DateTime $from, DateTime $to): Decimal
    {
        $total = $this
            ->getCustomerInvoiceSumQuery()
            ->setParameter('customer', $customer)
            ->setParameter('from', $from, Types::DATETIME_MUTABLE)
            ->setParameter('to', $to, Types::DATETIME_MUTABLE)
            ->getSingleScalarResult();

        return new Decimal($total ?: 0);
    }

    /**
     * @inheritDoc
     */
    public function getCustomerCreditSum(CustomerInterface $customer, DateTime $from, DateTime $to): Decimal
    {
        $total = $this
            ->getCustomerCreditSumQuery()
            ->setParameter('customer', $customer)
            ->setParameter('from', $from, Types::DATETIME_MUTABLE)
            ->setParameter('to', $to, Types::DATETIME_MUTABLE)
            ->getSingleScalarResult();

        return new Decimal($total ?: 0);
    }

    /**
     * @inheritDoc
     */
    public function getCustomerInvoiceCount(CustomerInterface $customer, DateTime $from, DateTime $to): int
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
    public function getCustomerCreditCount(CustomerInterface $customer, DateTime $from, DateTime $to): int
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
     */
    private function getDueInvoicesQueryBuilder(
        CustomerInterface $customer = null,
        string            $currency = null
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
     */
    private function getFallInvoicesQueryBuilder(
        CustomerInterface $customer = null,
        string            $currency = null
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
     * @throws Exception
     */
    private function addInvoicesQBParameters(
        QueryBuilder      $qb,
        CustomerInterface $customer = null,
        string            $currency = null
    ): void {
        $today = new DateTime();
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
     * Returns the "find by «created at» date" query.
     */
    private function getFindByCreatedAtQuery(): Query
    {
        if (null !== $this->findByCreatedAtQuery) {
            return $this->findByCreatedAtQuery;
        }

        $qb = $this->createQueryBuilder('i');
        $ex = $qb->expr();

        return $this->findByCreatedAtQuery = $qb
            ->andWhere($ex->between('i.createdAt', ':from', ':to'))
            ->getQuery()
            ->useQueryCache(true);
    }

    /**
     * Returns the "customer invoice sum query".
     */
    private function getCustomerInvoiceSumQuery(): Query
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
     */
    private function getCustomerCreditSumQuery(): Query
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
     */
    private function getCustomerInvoiceCountQuery(): Query
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
     */
    private function getCustomerCreditCountQuery(): Query
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
