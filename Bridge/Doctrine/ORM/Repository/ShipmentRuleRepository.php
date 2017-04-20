<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Repository;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Query;
use Ekyna\Component\Commerce\Common\Calculator\AmountCalculatorFactory;
use Ekyna\Component\Commerce\Common\Context\ContextProviderInterface;
use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Pricing\Model\VatDisplayModes;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentMethodInterface;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentRuleInterface;
use Ekyna\Component\Commerce\Shipment\Repository\ShipmentRuleRepositoryInterface;
use Ekyna\Component\Resource\Doctrine\ORM\Repository\ResourceRepository;

/**
 * Class ShipmentRuleRepository
 * @package Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Repository
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ShipmentRuleRepository extends ResourceRepository implements ShipmentRuleRepositoryInterface
{
    protected ContextProviderInterface $contextProvider;
    protected AmountCalculatorFactory  $calculatorFactory;
    protected ?Query                   $findOneBySaleQuery = null;


    /**
     * Sets the context provider.
     *
     * @param ContextProviderInterface $provider
     */
    public function setContextProvider(ContextProviderInterface $provider): void
    {
        $this->contextProvider = $provider;
    }

    /**
     * Sets the amount calculator.
     *
     * @param AmountCalculatorFactory $factory
     */
    public function setCalculatorFactory(AmountCalculatorFactory $factory): void
    {
        $this->calculatorFactory = $factory;
    }

    /**
     * @inheritDoc
     */
    public function findOneBySale(SaleInterface $sale, ShipmentMethodInterface $method = null): ?ShipmentRuleInterface
    {
        // Use sale's shipment method if not passed
        if (null === $method) {
            $method = $sale->getShipmentMethod();
        }
        if (null === $method) {
            return null;
        }

        $context = $this->contextProvider->getContext($sale);

        $result = $this->calculatorFactory->create()->calculateSaleItems($sale);

        $parameters = [
            'net_mode' => VatDisplayModes::MODE_NET,
            'net_base' => $result->getBase(),
            'ati_mode' => VatDisplayModes::MODE_ATI,
            'ati_base' => $result->getBase(true),
            'method'   => $method,
            'country'  => $context->getDeliveryCountry(),
            'group'    => $context->getCustomerGroup(),
        ];

        return $this
            ->getFindOneBySaleQuery()
            ->setParameters($parameters)
            ->setParameter('date', $context->getDate(), Types::DATETIME_MUTABLE)
            ->getOneOrNullResult();
    }

    /**
     * Returns the "find one by sale" query.
     *
     * @return Query
     */
    protected function getFindOneBySaleQuery(): Query
    {
        if (null !== $this->findOneBySaleQuery) {
            return $this->findOneBySaleQuery;
        }

        $qb = $this->createQueryBuilder('r');
        $e = $qb->expr();

        return $this->findOneBySaleQuery = $qb
            ->andWhere($e->orX(
                $e->andX(
                    $e->eq('r.vatMode', ':net_mode'),
                    $e->lte('r.baseTotal', ':net_base')
                ),
                $e->andX(
                    $e->eq('r.vatMode', ':ati_mode'),
                    $e->lte('r.baseTotal', ':ati_base')
                )
            ))
            ->andWhere($e->orX(
                'r.methods IS EMPTY',
                $e->isMemberOf(':method', 'r.methods')
            ))
            ->andWhere($e->orX(
                'r.countries IS EMPTY',
                $e->isMemberOf(':country', 'r.countries')
            ))
            ->andWhere($e->orX(
                'r.customerGroups IS EMPTY',
                $e->isMemberOf(':group', 'r.customerGroups')
            ))
            ->andWhere($e->orX(
                'r.startAt IS NULL',
                $e->lte('r.startAt', ':date')
            ))
            ->andWhere($e->orX(
                'r.endAt IS NULL',
                $e->gte('r.endAt', ':date')
            ))
            ->getQuery()
            ->setMaxResults(1)
            ->useQueryCache(true);
    }
}
