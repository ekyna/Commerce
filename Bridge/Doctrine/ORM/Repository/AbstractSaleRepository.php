<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Repository;

use Doctrine\ORM\QueryBuilder;
use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Common\Repository\SaleRepositoryInterface;
use Ekyna\Component\Commerce\Customer\Model\CustomerInterface;
use Ekyna\Component\Commerce\Subject\Model\SubjectInterface;
use Ekyna\Component\Resource\Doctrine\ORM\Repository\ResourceRepository;

/**
 * Class AbstractSaleRepository
 * @package Ekyna\Component\Commerce\Common\Repository
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
abstract class AbstractSaleRepository extends ResourceRepository implements SaleRepositoryInterface
{
    /**
     * @inheritDoc
     */
    public function findOneById(int $id): ?SaleInterface
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
     * @inheritDoc
     */
    public function findOneByKey(string $key): ?SaleInterface
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
     * @inheritDoc
     */
    public function findOneByNumber(string $number): ?SaleInterface
    {
        $qb = $this->getOneQueryBuilder('o');

        return $qb
            ->andWhere($qb->expr()->eq('o.number', ':number'))
            ->getQuery()
            ->useQueryCache(true)
            ->setParameter('number', $number)
            ->getOneOrNullResult();
    }

    /**
     * @inheritDoc
     */
    public function findByCustomer(CustomerInterface $customer, array $states = [], bool $withChildren = false): array
    {
        $qb = $this->createQueryBuilder('o');

        if ($withChildren && $customer->hasChildren()) {
            $qb->andWhere($qb->expr()->in('o.customer', ':customers'));
            $parameters = ['customers' => array_merge([$customer], $customer->getChildren()->toArray())];
        } else {
            $qb->andWhere($qb->expr()->eq('o.customer', ':customer'));
            $parameters = ['customer' => $customer];
        }

        $qb->addOrderBy('o.createdAt', 'DESC');

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
    public function findOneByCustomerAndNumber(CustomerInterface $customer, string $number): ?SaleInterface
    {
        $qb = $this->createQueryBuilder('o');

        $sale = $qb
            ->join('o.customer', 'c')
            ->andWhere($qb->expr()->orX(
                $qb->expr()->eq('c', ':customer'),
                $qb->expr()->eq('c.parent', ':customer')
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
     * @inheritDoc
     */
    public function findBySubject(SubjectInterface $subject, array $states = []): array
    {
        $qb = $this->createQueryBuilder('o');
        $qb
            ->join('o.items', 'i')
            ->leftJoin('i.children', 'c')
            ->leftJoin('c.children', 'sc')
            ->andWhere($qb->expr()->orX(
                $qb->expr()->andX(
                    $qb->expr()->eq('i.subjectIdentity.provider', ':provider'),
                    $qb->expr()->eq('i.subjectIdentity.identifier', ':identifier')
                ),
                $qb->expr()->andX(
                    $qb->expr()->eq('c.subjectIdentity.provider', ':provider'),
                    $qb->expr()->eq('c.subjectIdentity.identifier', ':identifier')
                ),
                $qb->expr()->andX(
                    $qb->expr()->eq('sc.subjectIdentity.provider', ':provider'),
                    $qb->expr()->eq('sc.subjectIdentity.identifier', ':identifier')
                )
            ));

        $parameters = [
            'provider'   => $subject::getProviderName(),
            'identifier' => $subject->getId(),
        ];

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
     * Returns the "find one result" query builder.
     *
     * @param string|null $alias
     * @param string|null $indexBy
     *
     * @return QueryBuilder
     */
    protected function getOneQueryBuilder(string $alias = null, string $indexBy = null): QueryBuilder
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
            ->leftJoin($alias . '.shipmentMethod', 'shipment_method')
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
    protected function loadLines(SaleInterface $sale): AbstractSaleRepository
    {
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

        return $this;
    }

    /**
     * Loads the sale payments.
     *
     * @param SaleInterface $sale
     *
     * @return $this|AbstractSaleRepository
     */
    protected function loadPayments(SaleInterface $sale): AbstractSaleRepository
    {
        $qb = $this->createQueryBuilder('o');
        $qb
            ->select('PARTIAL o.{id}', 'payment')
            ->leftJoin('o.payments', 'payment')
            ->andWhere($qb->expr()->eq('o.id', ':id'))
            ->getQuery()
            ->useQueryCache(true)
            ->setParameter('id', $sale->getId())
            ->getResult();

        return $this;
    }
}
