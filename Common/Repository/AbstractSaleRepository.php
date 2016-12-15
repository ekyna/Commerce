<?php

namespace Ekyna\Component\Commerce\Common\Repository;

use Ekyna\Component\Commerce\Common\Model\SaleInterface;
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
        $qb = $this->createQueryBuilder('o');

        $sale = $qb
            ->select(
                'o',
                'customer',
                'customer_group',
                'invoice_address',
                'delivery_address',
                'shipment_method',
                'currency'
            )
            ->leftJoin('o.customer', 'customer')
            ->leftJoin('o.customerGroup', 'customer_group')
            ->leftJoin('o.invoiceAddress', 'invoice_address')
            ->leftJoin('o.deliveryAddress', 'delivery_address')
            ->leftJoin('o.preferredShipmentMethod', 'shipment_method')
            ->leftJoin('o.currency', 'currency')
            ->andWhere($qb->expr()->eq('o.id', ':id'))
            ->setMaxResults(1)
            ->getQuery()
            ->useQueryCache(true)
            ->setParameter('id' , $id)
            ->getOneOrNullResult();

        if (null !== $sale) {
            $this
                ->loadLines($sale)
                ->loadPayments($sale);
        }

        return $sale;
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
