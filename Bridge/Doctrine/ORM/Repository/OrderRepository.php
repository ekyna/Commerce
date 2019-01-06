<?php

namespace Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Repository;

use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\Query;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;
use Ekyna\Component\Commerce\Customer\Model\CustomerInterface;
use Ekyna\Component\Commerce\Invoice\Model\InvoiceStates;
use Ekyna\Component\Commerce\Order\Model\OrderInterface;
use Ekyna\Component\Commerce\Order\Repository\OrderRepositoryInterface;
use Ekyna\Component\Commerce\Payment\Model\PaymentTermTriggers as Trigger;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentStates;

/**
 * Class OrderRepository
 * @package Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Repository
 * @author  Etienne Dauvergne <contact@ekyna.com>
 *
 * @method OrderInterface|null findOneById($id)
 * @method OrderInterface|null findOneByKey($key)
 * @method OrderInterface|null findOneByNumber($number)
 */
class OrderRepository extends AbstractSaleRepository implements OrderRepositoryInterface
{
    /**
     * @inheritdoc
     */
    public function existsForCustomer(CustomerInterface $customer)
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

    /**
     * @inheritdoc
     */
    public function findOneByCustomerAndNumber(CustomerInterface $customer, $number)
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

    /**
     * @inheritdoc
     */
    public function findByOriginCustomer(CustomerInterface $customer, array $states = [], $strict = false)
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

    /**
     * @inheritdoc
     */
    public function findDueOrders()
    {
        $qb = $this->createQueryBuilder('o');
        $ex = $qb->expr();

        $query = $qb
            ->leftJoin('o.paymentTerm', 't')
            ->andWhere($ex->eq('o.sample', ':not_sample'))// Not sample
            ->andWhere($ex->lt('o.paidTotal', 'o.grandTotal'))// Paid total lower than grand total
            ->andWhere($ex->orX(
                $ex->andX(                                               // Outstanding
                    $ex->isNotNull('o.paymentTerm'),                     // - With payment term
                    $this->getDueClauses(),                              // - Terms triggered
                    $qb->expr()->lte('o.outstandingDate',
                        ':today')      // - Payment limit date lower than or equal today
                ),
                $ex->andX(                                               // Regular
                    $ex->isNull('o.paymentTerm'),                        // - Without payment term
                    $ex->eq('o.shipmentState', ':state_fully_shipped')   // - Shipped
                )
            ))
            ->addOrderBy('o.outstandingDate', 'ASC')
            ->getQuery();

        $this->setDueParameters($query);

        return $query
            ->setParameter('not_sample', false)
            ->setParameter('today', (new \DateTime())->setTime(23, 59, 59), Type::DATETIME)
            ->useQueryCache(true)
            ->getResult();
    }

    /**
     * @inheritdoc
     */
    public function getRegularDue()
    {
        return $this
            ->getRegularDueQueryBuilder()
            ->select('SUM(o.grandTotal)-SUM(o.paidTotal)')
            ->getQuery()
            ->useQueryCache(true)
            //->useResultCache(true, 300)
            ->getSingleScalarResult();
    }

    /**
     * @inheritdoc
     */
    public function getRegularDueOrders()
    {
        return $this
            ->getRegularDueQueryBuilder()
            ->getQuery()
            ->useQueryCache(true)
            //->useResultCache(true, 300)
            ->getResult();
    }

    /**
     * @inheritdoc
     */
    public function getOutstandingExpiredDue()
    {
        return $this
            ->getOutstandingExpiredDueQueryBuilder()
            ->select('SUM(o.grandTotal)-SUM(o.paidTotal)')
            ->getQuery()
            ->useQueryCache(true)
            //->useResultCache(true, 300)
            ->getSingleScalarResult();
    }

    /**
     * @inheritdoc
     */
    public function getOutstandingExpiredDueOrders()
    {
        return $this
            ->getOutstandingExpiredDueQueryBuilder()
            ->getQuery()
            ->useQueryCache(true)
            //->useResultCache(true, 300)
            ->getResult();
    }

    /**
     * @inheritDoc
     */
    public function getOutstandingFallDue()
    {
        return $this
            ->getOutstandingFallDueQueryBuilder()
            ->select('SUM(o.grandTotal)-SUM(o.paidTotal)')
            ->getQuery()
            ->useQueryCache(true)
            //->useResultCache(true, 300)
            ->getSingleScalarResult();
    }

    /**
     * @inheritDoc
     */
    public function getOutstandingFallDueOrders()
    {
        return $this
            ->getOutstandingFallDueQueryBuilder()
            ->getQuery()
            ->useQueryCache(true)
            //->useResultCache(true, 300)
            ->getResult();
    }

    /**
     * @inheritdoc
     */
    public function getOutstandingPendingDue()
    {
        return $this
            ->getOutstandingPendingDueQueryBuilder()
            ->select('SUM(o.grandTotal)-SUM(o.paidTotal)')
            ->getQuery()
            ->useQueryCache(true)
            //->useResultCache(true, 300);
            ->getSingleScalarResult();
    }

    /**
     * @inheritdoc
     */
    public function getOutstandingPendingDueOrders()
    {
        return $this
            ->getOutstandingPendingDueQueryBuilder()
            ->getQuery()
            ->useQueryCache(true)
            //->useResultCache(true, 300);
            ->getResult();
    }

    /**
     * Returns the regular due query.
     *
     * @return QueryBuilder
     */
    private function getRegularDueQueryBuilder()
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
     *
     * @return QueryBuilder
     */
    private function getOutstandingExpiredDueQueryBuilder()
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
            ->setParameter('today', (new \DateTime())->setTime(23, 59, 59), Type::DATETIME)
            ->setParameter('canceled_or_refunded', [InvoiceStates::STATE_CANCELED, InvoiceStates::STATE_CREDITED]);

        $this->setDueParameters($qb);

        return $qb;
    }

    /**
     * Returns the outstanding fall due query builder.
     *
     * @return QueryBuilder
     */
    private function getOutstandingFallDueQueryBuilder()
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
            ->setParameter('today', (new \DateTime())->setTime(23, 59, 59), Type::DATETIME)
            ->setParameter('canceled_or_refunded', [InvoiceStates::STATE_CANCELED, InvoiceStates::STATE_CREDITED]);

        $this->setDueParameters($qb);

        return $qb;
    }

    /**
     * @inheritdoc
     */
    private function getOutstandingPendingDueQueryBuilder()
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
     *
     * @return Expr\Base
     */
    private function getDueClauses()
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

    /**
     * @inheritdoc
     */
    protected function getAlias()
    {
        return 'o';
    }
}
