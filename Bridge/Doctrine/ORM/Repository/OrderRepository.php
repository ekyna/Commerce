<?php

namespace Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Repository;

use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\Query;
use Doctrine\ORM\Query\Expr;
use Ekyna\Component\Commerce\Customer\Model\CustomerInterface;
use Ekyna\Component\Commerce\Order\Model\OrderInterface;
use Ekyna\Component\Commerce\Order\Repository\OrderRepositoryInterface;
use Ekyna\Component\Commerce\Payment\Model\PaymentTermTriggers as Trigger;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentStates;
use Ekyna\Component\Commerce\Invoice\Model\InvoiceStates;

/**
 * Class OrderRepository
 * @package Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Repository
 * @author  Etienne Dauvergne <contact@ekyna.com>
 *
 * @method OrderInterface|null findOneById($id)
 * @method OrderInterface|null findOneByKey($key)
 */
class OrderRepository extends AbstractSaleRepository implements OrderRepositoryInterface
{
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
     * @inheritDoc
     */
    public function findDueOrders()
    {
        $qb = $this->createQueryBuilder('o');
        $ex = $qb->expr();

        $query = $qb
            ->leftJoin('o.paymentTerm', 't')
            ->andWhere($ex->eq('o.sample', ':not_sample'))               // Not sample
            ->andWhere($ex->lt('o.paidTotal', 'o.grandTotal'))           // Paid total lower than grand total
            ->andWhere($ex->orX(
                $ex->andX(                                               // Outstanding
                    $ex->isNotNull('o.paymentTerm'),                     // - With payment term
                    $this->getDueClauses(),                              // - Terms triggered
                    $qb->expr()->lte('o.outstandingDate', ':today')      // - Payment limit date lower than or equal today
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
     * @return array
     */
    public function getRegularDue()
    {
        $qb = $this->createQueryBuilder('o');
        $ex = $qb->expr();

        $query = $qb
            ->select('SUM(o.grandTotal)-SUM(o.paidTotal)')
            ->where($ex->andX(
                $ex->eq('o.sample', ':not_sample'),                 // Not sample
                $ex->lt('o.paidTotal', 'o.grandTotal'),             // Paid total lower than grand total
                $ex->eq('o.shipmentState', ':state_fully_shipped'), // Shipped
                $ex->isNull('o.paymentTerm')                        // Without payment term
            // TODO not refunded ?
            ))
            ->getQuery();


        return $query
            ->setParameter('not_sample', false)
            ->setParameter('state_fully_shipped', ShipmentStates::STATE_COMPLETED)
            ->useQueryCache(true)
            //->useResultCache(true, 300)
            ->getSingleScalarResult();
    }

    /**
     * @inheritDoc
     */
    public function getOutstandingExpiredDue()
    {
        $qb = $this->createQueryBuilder('o');
        $ex = $qb->expr();

        $query = $qb
            ->join('o.paymentTerm', 't')
            ->select('SUM(o.grandTotal)-SUM(o.paidTotal)')
            //->select('SUM(o.outstandingExpired)')
            ->where($ex->andX(
                $ex->eq('o.sample', ':not_sample'),               // Not sample
                $ex->lt('o.paidTotal', 'o.grandTotal'),           // Paid total lower than grand total
                $qb->expr()->lte('o.outstandingDate', ':today'),  // Payment limit date lower than today
                $this->getDueClauses()                            // Terms triggered
            ))
            ->getQuery();

        $this->setDueParameters($query);

        return $query
            ->setParameter('not_sample', false)
            ->setParameter('today', (new \DateTime())->setTime(23, 59, 59), Type::DATETIME)
            ->useQueryCache(true)
            //->useResultCache(true, 300)
            ->getSingleScalarResult();
    }

    /**
     * @inheritDoc
     */
    public function getOutstandingToGoDue()
    {
        $qb = $this->createQueryBuilder('o');
        $ex = $qb->expr();

        $query = $qb
            ->join('o.paymentTerm', 't')
            ->select('SUM(o.grandTotal)-SUM(o.paidTotal)')
            //->select('SUM(o.outstandingAccepted)')
            ->where($ex->andX(
                $ex->eq('o.sample', ':not_sample'),               // Not sample
                $ex->lt('o.paidTotal', 'o.grandTotal'),           // Paid total lower than grand total
                $qb->expr()->gt('o.outstandingDate', ':today'),   // Payment limit date greater than today
                $this->getDueClauses()                            // Terms triggered
            ))
            ->getQuery();

        $this->setDueParameters($query);

        return $query
            ->setParameter('not_sample', false)
            ->setParameter('today', (new \DateTime())->setTime(23, 59, 59), Type::DATETIME)
            ->useQueryCache(true)
            //->useResultCache(true, 300)
            ->getSingleScalarResult();
    }

    /**
     * @inheritDoc
     */
    public function getOutstandingPendingDue()
    {
        $qb = $this->createQueryBuilder('o');
        $ex = $qb->expr();

        $query = $qb
            ->join('o.paymentTerm', 't')
            ->select('SUM(o.grandTotal)-SUM(o.paidTotal)')
            //->select('SUM(o.outstandingAccepted)')
            ->where($ex->andX(
                $ex->eq('o.sample', ':not_sample'),               // Not sample
                $ex->lt('o.paidTotal', 'o.grandTotal'),           // Paid total lower than grand total
                $ex->not($this->getDueClauses())                  // Terms not triggered
            ))
            ->getQuery();

        $this->setDueParameters($query);

        return $query
            ->setParameter('not_sample', false)
            ->useQueryCache(true)
            //->useResultCache(true, 300);
            ->getSingleScalarResult();
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
     * @param Query $query
     */
    private function setDueParameters(Query $query)
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
