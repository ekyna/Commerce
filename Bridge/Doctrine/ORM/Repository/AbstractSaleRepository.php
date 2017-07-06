<?php

namespace Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Repository;

use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Common\Repository\SaleRepositoryInterface;
use Ekyna\Component\Commerce\Customer\Model\CustomerInterface;
use Ekyna\Component\Resource\Doctrine\ORM\ResourceRepository;

/**
 * Class AbstractSaleRepository
 * @package Ekyna\Component\Commerce\Common\Repository
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
abstract class AbstractSaleRepository extends ResourceRepository implements SaleRepositoryInterface
{
    /**
     * @inheritdoc
     */
    public function findOneById($id)
    {
        $qb = $this->getOneQueryBuilder('o');

        $sale = $qb
            ->andWhere($qb->expr()->eq('o.id', ':id'))
            ->getQuery()
            ->useQueryCache(true)
            ->setParameter('id', $id)
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
    public function findOneByKey($key)
    {
        $qb = $this->getOneQueryBuilder('o');

        $sale = $qb
            ->andWhere($qb->expr()->eq('o.key', ':key'))
            ->getQuery()
            ->useQueryCache(true)
            ->setParameter('key', $key)
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
    public function findByCustomer(CustomerInterface $customer, array $states = [])
    {
        $qb = $this->createQueryBuilder('o');
        $qb
            ->andWhere($qb->expr()->eq('o.customer', ':customer'))
            ->addOrderBy('o.createdAt', 'DESC');

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
    public function findOneByCustomerAndNumber(CustomerInterface $customer, $number)
    {
        $qb = $this->createQueryBuilder('o');

        $sale = $qb
            ->andWhere($qb->expr()->eq('o.customer', ':customer'))
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
     * Returns the "find one result" query builder.
     *
     * @param string $alias
     * @param string $indexBy
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    protected function getOneQueryBuilder($alias = null, $indexBy = null)
    {
        return $this
            ->createQueryBuilder($alias, $indexBy)
            ->select(
                $alias,
                'customer',
                'customer_group',
                'invoice_address',
                'delivery_address',
                'shipment_method',
                'currency'
            )
            ->leftJoin($alias . '.customer', 'customer')
            ->leftJoin($alias . '.customerGroup', 'customer_group')
            ->leftJoin($alias . '.invoiceAddress', 'invoice_address')
            ->leftJoin($alias . '.deliveryAddress', 'delivery_address')
            ->leftJoin($alias . '.preferredShipmentMethod', 'shipment_method')
            ->leftJoin($alias . '.currency', 'currency')
            ->setMaxResults(1);
    }

    /**
     * Loads the sale lines (items / adjustments).
     *
     * @param SaleInterface $sale
     *
     * @return $this|AbstractSaleRepository
     */
    protected function loadLines(SaleInterface $sale)
    {
        if (null !== $sale) {
            $qb = $this->createQueryBuilder('o');
            $qb
                ->select('PARTIAL o.{id}', 'item', 'adjustment', 'sub_item', 'sub_adjustment')
                ->leftJoin('o.items', 'item')
                ->leftJoin('item.adjustments', 'adjustment')
                ->leftJoin('item.children', 'sub_item')
                ->leftJoin('sub_item.adjustments', 'sub_adjustment')
                ->andWhere($qb->expr()->eq('o.id', ':id'))
                ->getQuery()
                ->useQueryCache(true)
                ->setParameter('id', $sale->getId())
                ->getResult();
        }

        return $this;
    }

    /**
     * Loads the sale payments.
     *
     * @param SaleInterface $sale
     *
     * @return $this|AbstractSaleRepository
     */
    protected function loadPayments(SaleInterface $sale)
    {
        if (null !== $sale) {
            $qb = $this->createQueryBuilder('o');
            $qb
                ->select('PARTIAL o.{id}', 'payment')
                ->leftJoin('o.payments', 'payment')
                ->andWhere($qb->expr()->eq('o.id', ':id'))
                ->getQuery()
                ->useQueryCache(true)
                ->setParameter('id', $sale->getId())
                ->getResult();
        }

        return $this;
    }
}
