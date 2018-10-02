<?php

namespace Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Repository;

use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\Query\Expr;
use Ekyna\Component\Commerce\Customer\Model\CustomerInterface;
use Ekyna\Component\Commerce\Order\Model\OrderInterface;
use Ekyna\Component\Commerce\Order\Repository\OrderRepositoryInterface;
use Ekyna\Component\Commerce\Payment\Model\PaymentStates;
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

        return $qb
            // Shipped
            ->andWhere($ex->eq('o.shipmentState', ':shipment_state'))
            // Not paid
            ->andWhere($ex->lt('o.paidTotal', 'o.grandTotal'))
            // Not refunded
            ->andWhere($ex->neq('o.paymentState', ':payment_state'))
            // Does not have outstanding date greater than today
            ->andWhere($ex->not($ex->andX(
                $ex->isNotNull('o.outstandingDate'),
                $ex->gt('o.outstandingDate', ':today')
            )))
            ->addOrderBy('o.createdAt', 'ASC')
            ->getQuery()
            ->useQueryCache(true)
            ->setParameter('shipment_state', ShipmentStates::STATE_COMPLETED)
            ->setParameter('payment_state', PaymentStates::STATE_REFUNDED)
            ->setParameter('today', (new \DateTime())->setTime(23, 59, 59), Type::DATETIME)
            ->getResult();
    }

    /**
     * @inheritDoc
     */
    public function getCustomersExpiredDue()
    {
        $qb = $this->createQueryBuilder('o');

        return $qb
            ->select('SUM(o.outstandingExpired)')
            ->getQuery()
            ->useQueryCache(true)
            ->getSingleScalarResult();
    }

    /**
     * @inheritDoc
     */
    public function getCustomersFallDue()
    {
        $qb = $this->createQueryBuilder('o');

        return $qb
            ->join('o.paymentTerm', 't')
            ->select('SUM(o.outstandingAccepted)')
            ->where($this->getDueClauses($qb->expr()))
            ->getQuery()
            ->setParameters($this->getDueParameters())
            ->useQueryCache(true)
            ->useResultCache(true, 300)
            ->getSingleScalarResult();
    }

    /**
     * @inheritDoc
     */
    public function getCustomersPendingDue()
    {
        $qb = $this->createQueryBuilder('o');

        return $qb
            ->join('o.paymentTerm', 't')
            ->select('SUM(o.outstandingAccepted)')
            ->where($qb->expr()->not($this->getDueClauses($qb->expr())))
            ->getQuery()
            ->setParameters($this->getDueParameters())
            ->useQueryCache(true)
            ->useResultCache(true, 300)
            ->getSingleScalarResult();
    }

    /**
     * Returns the due clause (payment term triggers VS order states).
     *
     * @param Expr $ex
     *
     * @return Expr\Orx
     */
    private function getDueClauses(Expr $ex)
    {
        return $ex->orX(
            $ex->andX(
                $ex->eq('t.trigger', ':trigger1'),
                $ex->in('o.invoiceState', ':state1')
            ),
            $ex->andX(
                $ex->eq('t.trigger', ':trigger2'),
                $ex->eq('o.invoiceState', ':state2')
            ),
            $ex->andX(
                $ex->eq('t.trigger', ':trigger3'),
                $ex->in('o.shipmentState', ':state3')
            ),
            $ex->andX(
                $ex->eq('t.trigger', ':trigger4'),
                $ex->eq('o.shipmentState', ':state4')
            )
        );
    }

    /**
     * Returns the due clause's parameters.
     *
     * @return array
     */
    private function getDueParameters()
    {
        return [
            'trigger1' => Trigger::TRIGGER_INVOICED,
            'state1'   => [InvoiceStates::STATE_PARTIAL, InvoiceStates::STATE_COMPLETED],
            'trigger2' => Trigger::TRIGGER_FULLY_INVOICED,
            'state2'   => InvoiceStates::STATE_COMPLETED,
            'trigger3' => Trigger::TRIGGER_SHIPPED,
            'state3'   => [ShipmentStates::STATE_PARTIAL, ShipmentStates::STATE_COMPLETED],
            'trigger4' => Trigger::TRIGGER_FULLY_SHIPPED,
            'state4'   => ShipmentStates::STATE_COMPLETED,
        ];
    }

    /**
     * @inheritdoc
     */
    protected function getAlias()
    {
        return 'o';
    }
}
