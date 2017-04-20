<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Repository;

use DateTime;
use Decimal\Decimal;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Query;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;
use Ekyna\Component\Commerce\Common\Model\CouponInterface;
use Ekyna\Component\Commerce\Customer\Model\CustomerInterface;
use Ekyna\Component\Commerce\Invoice\Model\InvoiceStates;
use Ekyna\Component\Commerce\Order\Model\OrderInterface;
use Ekyna\Component\Commerce\Order\Model\OrderStates;
use Ekyna\Component\Commerce\Order\Repository\OrderRepositoryInterface;
use Ekyna\Component\Commerce\Payment\Model\PaymentTermTriggers as Trigger;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentStates;

/**
 * Class OrderRepository
 * @package Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Repository
 * @author  Etienne Dauvergne <contact@ekyna.com>
 *
 * @method OrderInterface|null findOneById(int $id)
 * @method OrderInterface|null findOneByKey(string $key)
 * @method OrderInterface|null findOneByNumber(string $number)
 */
class OrderRepository extends AbstractSaleRepository implements OrderRepositoryInterface
{
    public function existsForCustomer(CustomerInterface $customer): bool
    {
        $qb = $this->createQueryBuilder('o');

        $id = $qb
            ->select('o.id')
            ->andWhere($qb->expr()->eq('o.customer', ':customer'))
            ->getQuery()
            ->setMaxResults(1)
            ->setParameters([
                'customer' => $customer,
            ])
            ->getOneOrNullResult(Query::HYDRATE_SINGLE_SCALAR);

        return null !== $id;
    }

    public function existsForEmail(string $email): bool
    {
        $qb = $this->createQueryBuilder('o');

        return null !== $qb
                ->select('o.id')
                ->andWhere($qb->expr()->eq('o.email', ':email'))
                ->getQuery()
                ->setMaxResults(1)
                ->setParameters([
                    'email' => $email,
                ])
                ->getOneOrNullResult(Query::HYDRATE_SINGLE_SCALAR);
    }

    public function findOneByCustomerAndNumber(CustomerInterface $customer, string $number): ?OrderInterface
    {
        $qb = $this->createQueryBuilder('o');

        $sale = $qb
            ->join('o.customer', 'c')
            ->andWhere($qb->expr()->orX(
                $qb->expr()->eq('c', ':customer'),
                $qb->expr()->eq('c.parent', ':customer'),
                $qb->expr()->eq('o.originCustomer', ':customer')
            ))
            ->andWhere($qb->expr()->eq('o.number', ':number'))
            ->getQuery()
            ->setParameters([
                'customer' => $customer,
                'number'   => $number,
            ])
            ->getOneOrNullResult();

        if (null !== $sale) {
            $this
                ->loadLines($sale)
                ->loadPayments($sale);
        }

        return $sale;
    }

    public function findByOriginCustomer(CustomerInterface $customer, array $states = [], bool $strict = false): array
    {
        $qb = $this->createQueryBuilder('o');

        if ($strict) {
            $qb->andWhere($qb->expr()->eq('o.originCustomer', ':customer'));
        } else {
            $qb->andWhere($qb->expr()->orX(
                $qb->expr()->eq('o.customer', ':customer'),
                $qb->expr()->eq('o.originCustomer', ':customer')
            ));
        }

        $qb->addOrderBy('o.createdAt', 'DESC');

        $parameters = ['customer' => $customer];

        if (!empty($states)) {
            $qb->andWhere($qb->expr()->in('o.state', ':states'));

            $parameters['states'] = $states;
        }

        return $qb
            ->getQuery()
            ->setParameters($parameters)
            ->getResult();
    }

    public function findCompletedYesterday(): array
    {
        $start = (new DateTime('-1 day'))->setTime(0, 0);
        $end = (clone $start)->setTime(23, 59, 59, 999999);

        $qb = $this->createQueryBuilder('o');

        return $qb
            ->andWhere($qb->expr()->between('o.completedAt', ':start', ':end'))
            ->andWhere($qb->expr()->eq('o.state', ':state'))
            ->getQuery()
            ->setParameters([
                'start' => $start,
                'end'   => $end,
                'state' => OrderStates::STATE_COMPLETED,
            ])
            ->getResult();
    }

    public function findDueOrders(): array
    {
        $qb = $this->createQueryBuilder('o');
        $ex = $qb->expr();

        $query = $qb
            ->leftJoin('o.paymentTerm', 't')
            ->andWhere($ex->eq('o.sample', ':not_sample'))             // Not sample
            ->andWhere($ex->lt('o.paidTotal', 'o.grandTotal'))         // Paid total lower than grand total
            ->andWhere($ex->orX(
                $ex->andX(                                             // Outstanding
                    $ex->isNotNull('o.paymentTerm'),                   // - With payment term
                    $this->getDueClauses(),                            // - Terms triggered
                    $qb->expr()->lte('o.outstandingDate', ':today')    // - Payment limit date lower than or equal today
                ),
                $ex->andX(                                             // Regular
                    $ex->isNull('o.paymentTerm'),                      // - Without payment term
                    $ex->eq('o.shipmentState', ':state_fully_shipped') // - Shipped
                )
            ))
            ->addOrderBy('o.outstandingDate', 'ASC')
            ->getQuery();

        $this->setDueParameters($query);

        return $query
            ->setParameter('not_sample', false)
            ->setParameter('today', (new DateTime())->setTime(23, 59, 59, 999999), Types::DATETIME_MUTABLE)
            ->useQueryCache(true)
            ->getResult();
    }

    public function findWithNullRevenueOrMargin(): array
    {
        $qb = $this->createQueryBuilder('o');

        $ids = $qb
            ->select('o.id')
            ->orWhere($qb->expr()->isNull('o.revenueTotal'))
            ->orWhere($qb->expr()->isNull('o.marginTotal'))
            ->getQuery()
            ->getScalarResult();

        if (empty($ids)) {
            return [];
        }

        return array_map(function ($id) {
            return (int)$id;
        }, array_column($ids, 'id'));
    }

    public function getRegularDue(): Decimal
    {
        $total = $this
            ->getRegularDueQueryBuilder()
            ->select('SUM(o.grandTotal - o.paidTotal)')
            ->getQuery()
            ->useQueryCache(true)
            ->enableResultCache(300)
            ->getSingleScalarResult();

        return new Decimal($total ?: 0);
    }

    public function getRegularDueOrders(): array
    {
        return $this
            ->getRegularDueQueryBuilder()
            ->getQuery()
            ->enableResultCache(300)
            ->getResult();
    }

    public function getOutstandingExpiredDue(): Decimal
    {
        $total = $this
            ->getOutstandingExpiredDueQueryBuilder()
            ->select('SUM(o.grandTotal - o.paidTotal)')
            ->getQuery()
            ->useQueryCache(true)
            ->enableResultCache(300)
            ->getSingleScalarResult();

        return new Decimal($total ?: 0);
    }

    public function getOutstandingExpiredDueOrders(): array
    {
        return $this
            ->getOutstandingExpiredDueQueryBuilder()
            ->getQuery()
            ->useQueryCache(true)
            ->enableResultCache(300)
            ->getResult();
    }

    public function getOutstandingFallDue(): Decimal
    {
        $total = $this
            ->getOutstandingFallDueQueryBuilder()
            ->select('SUM(o.grandTotal - o.paidTotal)')
            ->getQuery()
            ->useQueryCache(true)
            ->enableResultCache(300)
            ->getSingleScalarResult();

        return new Decimal($total ?: 0);
    }

    public function getOutstandingFallDueOrders(): array
    {
        return $this
            ->getOutstandingFallDueQueryBuilder()
            ->getQuery()
            ->enableResultCache(300)
            ->getResult();
    }

    public function getOutstandingPendingDue(): Decimal
    {
        $total = $this
            ->getOutstandingPendingDueQueryBuilder()
            ->select('SUM(o.grandTotal - o.paidTotal)')
            ->getQuery()
            ->useQueryCache(true)
            ->enableResultCache(300)
            ->getSingleScalarResult();

        return new Decimal($total ?: 0);
    }

    public function getOutstandingPendingDueOrders(): array
    {
        return $this
            ->getOutstandingPendingDueQueryBuilder()
            ->getQuery()
            ->useQueryCache(true)
            ->getResult();
    }

    public function getRemainingTotal(): Decimal
    {
        $total = $this
            ->getRemainingQueryBuilder()
            ->select('SUM(o.grandTotal - o.invoiceTotal)')
            ->getQuery()
            ->useQueryCache(true)
            ->enableResultCache(300)
            ->getSingleScalarResult();

        return new Decimal($total ?: 0);
    }

    public function getRemainingOrders(): array
    {
        return $this
            ->getRemainingQueryBuilder()
            ->getQuery()
            ->useQueryCache(true)
            ->getResult();
    }

    public function getCustomerCurrencies(CustomerInterface $customer): array
    {
        $qb = $this->createQueryBuilder('o');
        $qb
            ->select(['c.code', 'COUNT(c.id) as num'])
            ->join('o.currency', 'c')
            ->andWhere($qb->expr()->eq('o.customer', ':customer'))
            ->groupBy('o.currency')
            ->addOrderBy('num', 'DESC')
            ->setParameter('customer', $customer);

        return array_column($qb->getQuery()->getScalarResult(), 'code');
    }

    public function getCouponUsage(CouponInterface $coupon): int
    {
        $qb = $this->createQueryBuilder('o');

        return (int)$qb
            ->select('COUNT(o.id)')
            ->andWhere($qb->expr()->eq('o.coupon', ':coupon'))
            ->getQuery()
            ->setParameter('coupon', $coupon)
            ->getSingleScalarResult();
    }

    /**
     * Returns the remaining query builder.
     */
    private function getRemainingQueryBuilder(): QueryBuilder
    {
        $qb = $this->createQueryBuilder('o');
        $ex = $qb->expr();

        $qb
            ->select('o')
            ->where($ex->andX(
                $ex->eq('o.sample', ':sample'),           // Not sample
                $ex->eq('o.state', ':state'),             // Accepted
                $ex->lt('o.invoiceTotal', 'o.grandTotal') // invoice total lower than grand total
            ))
            ->addOrderBy('o.createdAt', 'ASC')
            ->setParameter('sample', false)
            ->setParameter('state', OrderStates::STATE_ACCEPTED);

        return $qb;
    }

    /**
     * Returns the regular due query builder.
     */
    private function getRegularDueQueryBuilder(): QueryBuilder
    {
        $qb = $this->createQueryBuilder('o');
        $ex = $qb->expr();

        return $qb
            ->where($ex->andX(
                $ex->eq('o.sample', ':not_sample'),                    // Not sample
                $ex->lt('o.paidTotal', 'o.grandTotal'),                // Paid total lower than grand total
                $ex->notIn('o.invoiceState', ':canceled_or_refunded'), // Not canceled/refunded
                $ex->eq('o.shipmentState', ':shipped'),                // Shipped
                $ex->isNull('o.paymentTerm')                           // Without payment term
            ))
            ->addOrderBy('o.createdAt', 'ASC')
            ->setParameter('not_sample', false)
            ->setParameter('shipped', ShipmentStates::STATE_COMPLETED)
            ->setParameter('canceled_or_refunded', [InvoiceStates::STATE_CANCELED, InvoiceStates::STATE_CREDITED]);
    }

    /**
     * Returns the outstanding expired due query builder.
     */
    private function getOutstandingExpiredDueQueryBuilder(): QueryBuilder
    {
        $qb = $this->createQueryBuilder('o');
        $ex = $qb->expr();

        $qb
            ->join('o.paymentTerm', 't')
            ->where($ex->andX(
                $ex->eq('o.sample', ':not_sample'),                    // Not sample
                $ex->lt('o.paidTotal', 'o.grandTotal'),                // Paid total lower than grand total
                $ex->notIn('o.invoiceState', ':canceled_or_refunded'), // Not canceled/refunded
                $qb->expr()->lte('o.outstandingDate', ':today'),       // Payment limit date lower than today
                $this->getDueClauses()                                 // Terms triggered
            ))
            ->addOrderBy('o.outstandingDate', 'ASC')
            ->setParameter('not_sample', false)
            ->setParameter('today', (new DateTime())->setTime(23, 59, 59, 999999), Types::DATETIME_MUTABLE)
            ->setParameter('canceled_or_refunded', [InvoiceStates::STATE_CANCELED, InvoiceStates::STATE_CREDITED]);

        $this->setDueParameters($qb);

        return $qb;
    }

    /**
     * Returns the outstanding fall due query builder.
     */
    private function getOutstandingFallDueQueryBuilder(): QueryBuilder
    {
        $qb = $this->createQueryBuilder('o');
        $ex = $qb->expr();

        $qb
            ->join('o.paymentTerm', 't')
            ->where($ex->andX(
                $ex->eq('o.sample', ':not_sample'),                    // Not sample
                $ex->lt('o.paidTotal', 'o.grandTotal'),                // Paid total lower than grand total
                $ex->notIn('o.invoiceState', ':canceled_or_refunded'), // Not canceled/refunded
                $qb->expr()->gt('o.outstandingDate', ':today'),        // Payment limit date greater than today
                $this->getDueClauses()                                 // Terms triggered
            ))
            ->addOrderBy('o.outstandingDate', 'ASC')
            ->setParameter('not_sample', false)
            ->setParameter('today', (new DateTime())->setTime(23, 59, 59, 999999), Types::DATETIME_MUTABLE)
            ->setParameter('canceled_or_refunded', [InvoiceStates::STATE_CANCELED, InvoiceStates::STATE_CREDITED]);

        $this->setDueParameters($qb);

        return $qb;
    }

    /**
     * Returns the outstanding pending due query builder.
     */
    private function getOutstandingPendingDueQueryBuilder(): QueryBuilder
    {
        $qb = $this->createQueryBuilder('o');
        $ex = $qb->expr();

        $qb
            ->join('o.paymentTerm', 't')
            ->where($ex->andX(
                $ex->eq('o.sample', ':not_sample'),                    // Not sample
                $ex->lt('o.paidTotal', 'o.grandTotal'),                // Paid total lower than grand total
                $ex->notIn('o.invoiceState', ':canceled_or_refunded'), // Not canceled/refunded
                $ex->not($this->getDueClauses())                       // Terms not triggered
            ))
            ->addOrderBy('o.createdAt', 'ASC')
            ->setParameter('not_sample', false)
            ->setParameter('canceled_or_refunded', [InvoiceStates::STATE_CANCELED, InvoiceStates::STATE_CREDITED]);

        $this->setDueParameters($qb);

        return $qb;
    }

    /**
     * Returns the due clause:
     * - payment term triggered
     * - payment date lower than or equal today
     */
    private function getDueClauses(): Expr\Base
    {
        $ex = new Expr();

        return $ex->orX(
            $ex->andX(
                $ex->eq('t.trigger', ':trigger_invoiced'),
                $ex->in('o.invoiceState', ':state_invoiced')
            ),
            $ex->andX(
                $ex->eq('t.trigger', ':trigger_fully_invoiced'),
                $ex->eq('o.invoiceState', ':state_fully_invoiced')
            ),
            $ex->andX(
                $ex->eq('t.trigger', ':trigger_shipped'),
                $ex->in('o.shipmentState', ':state_shipped')
            ),
            $ex->andX(
                $ex->eq('t.trigger', ':trigger_fully_shipped'),
                $ex->eq('o.shipmentState', ':state_fully_shipped')
            )
        );
    }

    /**
     * Set the due clause's parameters.
     *
     * @param Query|QueryBuilder $query
     */
    private function setDueParameters($query)
    {
        $query
            ->setParameter('trigger_invoiced', Trigger::TRIGGER_INVOICED)
            ->setParameter('state_invoiced', [InvoiceStates::STATE_PARTIAL, InvoiceStates::STATE_COMPLETED])
            ->setParameter('trigger_fully_invoiced', Trigger::TRIGGER_FULLY_INVOICED)
            ->setParameter('state_fully_invoiced', InvoiceStates::STATE_COMPLETED)
            ->setParameter('trigger_shipped', Trigger::TRIGGER_SHIPPED)
            ->setParameter('state_shipped', [ShipmentStates::STATE_PARTIAL, ShipmentStates::STATE_COMPLETED])
            ->setParameter('trigger_fully_shipped', Trigger::TRIGGER_FULLY_SHIPPED)
            ->setParameter('state_fully_shipped', ShipmentStates::STATE_COMPLETED);
    }

    protected function getAlias(): string
    {
        return 'o';
    }
}
