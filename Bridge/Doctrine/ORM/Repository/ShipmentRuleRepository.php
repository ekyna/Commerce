<?php

namespace Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Repository;

use Doctrine\DBAL\Types\Type;
use Ekyna\Component\Commerce\Common\Calculator\AmountCalculatorInterface;
use Ekyna\Component\Commerce\Common\Context\ContextProviderInterface;
use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Pricing\Model\VatDisplayModes;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentMethodInterface;
use Ekyna\Component\Commerce\Shipment\Repository\ShipmentRuleRepositoryInterface;
use Ekyna\Component\Resource\Doctrine\ORM\ResourceRepository;

/**
 * Class ShipmentRuleRepository
 * @package Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Repository
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ShipmentRuleRepository extends ResourceRepository implements ShipmentRuleRepositoryInterface
{
    /**
     * @var ContextProviderInterface
     */
    protected $contextProvider;

    /**
     * @var AmountCalculatorInterface
     */
    protected $amountCalculator;

    /**
     * @var \Doctrine\ORM\Query
     */
    protected $findOneBySaleQuery;


    /**
     * Sets the context provider.
     *
     * @param ContextProviderInterface $provider
     */
    public function setContextProvider(ContextProviderInterface $provider)
    {
        $this->contextProvider = $provider;
    }

    /**
     * Sets the amount calculator.
     *
     * @param AmountCalculatorInterface $calculator
     */
    public function setAmountCalculator(AmountCalculatorInterface $calculator)
    {
        $this->amountCalculator = $calculator;
    }

    /**
     * @inheritdoc
     */
    public function findOneBySale(SaleInterface $sale, ShipmentMethodInterface $method = null)
    {
        // Use sale's shipment method if not passed
        if (null === $method) {
            $method = $sale->getShipmentMethod();
        }
        if (null === $method) {
            return null;
        }

        $context = $this->contextProvider->getContext($sale);

        $result = $this->amountCalculator->calculateSaleItems($sale);

        $parameters = [
            'net_mode'  => VatDisplayModes::MODE_NET,
            'net_gross' => $result->getGross(),
            'ati_mode'  => VatDisplayModes::MODE_ATI,
            'ati_gross' => $result->getGross(true),
            'method'    => $method,
            'country'   => $context->getDeliveryCountry(),
            'group'     => $context->getCustomerGroup(),
        ];

        return $this
            ->getFindOneBySaleQuery()
            ->setParameters($parameters)
            ->setParameter('date', $context->getDate(), Type::DATE)
            ->getOneOrNullResult();
    }

    /**
     * Returns the "find one by sale" query.
     *
     * @return \Doctrine\ORM\Query
     */
    protected function getFindOneBySaleQuery()
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
                    $e->lte('r.grossTotal', ':net_gross')
                ),
                $e->andX(
                    $e->eq('r.vatMode', ':ati_mode'),
                    $e->lte('r.grossTotal', ':ati_gross')
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
