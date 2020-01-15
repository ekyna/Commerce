<?php

namespace Ekyna\Component\Commerce\Shipment\Resolver;

use Ekyna\Component\Commerce\Common\Context\ContextProviderInterface;
use Ekyna\Component\Commerce\Common\Model\CountryInterface;
use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Exception\RuntimeException;
use Ekyna\Component\Commerce\Pricing\Model\TaxInterface;
use Ekyna\Component\Commerce\Pricing\Resolver\TaxResolverInterface;
use Ekyna\Component\Commerce\Shipment\Gateway\RegistryInterface;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentMethodInterface;
use Ekyna\Component\Commerce\Shipment\Model\ResolvedShipmentPrice;
use Ekyna\Component\Commerce\Shipment\Repository\ShipmentPriceRepositoryInterface;
use Ekyna\Component\Commerce\Shipment\Repository\ShipmentRuleRepositoryInterface;

/**
 * Class ShipmentPriceResolver
 * @package Ekyna\Component\Commerce\Shipment\Resolver
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ShipmentPriceResolver implements ShipmentPriceResolverInterface
{
    /**
     * @var ShipmentPriceRepositoryInterface
     */
    private $priceRepository;

    /**
     * @var ShipmentRuleRepositoryInterface
     */
    private $ruleRepository;

    /**
     * @var RegistryInterface
     */
    private $gatewayRegistry;

    /**
     * @var TaxResolverInterface
     */
    private $taxResolver;

    /**
     * @var ContextProviderInterface
     */
    private $contextProvider;

    /**
     * [
     *     {countryId} => [
     *         [
     *             'method'     => (ShipmentMethodInterface),
     *             'max_weight' => (float),
     *             'prices'     => [
     *                 [
     *                     'weight' => (float),
     *                     'price'  => (float),
     *                 ]
     *             ],
     *         ]
     *     ],
     * ]
     *
     * @var array
     */
    private $grids = [];


    /**
     * Constructor.
     *
     * @param ShipmentPriceRepositoryInterface $priceRepository
     * @param ShipmentRuleRepositoryInterface  $ruleRepository
     * @param RegistryInterface                $gatewayRegistry
     * @param TaxResolverInterface             $taxResolver
     * @param ContextProviderInterface         $contextProvider
     */
    public function __construct(
        ShipmentPriceRepositoryInterface $priceRepository,
        ShipmentRuleRepositoryInterface $ruleRepository,
        RegistryInterface $gatewayRegistry,
        TaxResolverInterface $taxResolver,
        ContextProviderInterface $contextProvider
    ) {
        $this->priceRepository = $priceRepository;
        $this->ruleRepository = $ruleRepository;
        $this->gatewayRegistry = $gatewayRegistry;
        $this->taxResolver = $taxResolver;
        $this->contextProvider = $contextProvider;
    }

    /**
     * @inheritdoc
     */
    public function hasFreeShipping(SaleInterface $sale, ShipmentMethodInterface $method = null)
    {
        return null !== $this->ruleRepository->findOneBySale($sale, $method);
    }

    /**
     * @inheritdoc
     */
    public function getAvailablePricesBySale(SaleInterface $sale, $availableOnly = true)
    {
        $country = $this->contextProvider->getContext($sale)->getDeliveryCountry();

        $prices = [];

        $grid = $this->getGridForCountry($country);

        $weight = $sale->getShipmentWeight() ?? $sale->getWeightTotal();

        foreach ($grid as $entry) {
            /** @var ShipmentMethodInterface $method */
            $method = $entry['method'];
            if ($availableOnly && !$method->isAvailable()) {
                continue;
            }

            $resolvedPrice = new ResolvedShipmentPrice($method, $weight);

            if ($rule = $this->ruleRepository->findOneBySale($sale, $method)) {
                $price = $rule->getNetPrice();
            } else {
                $price = $this->resolvePrice($entry, $weight);
            }

            $resolvedPrice
                ->setPrice($price)
                ->setTaxes($this->getTaxesRates($method, $country));

            $prices[] = $resolvedPrice;
        }

        return $prices;
    }

    /**
     * @inheritdoc
     */
    public function getPriceBySale(SaleInterface $sale)
    {
        if (null === $method = $sale->getShipmentMethod()) {
            throw new RuntimeException("Sale's shipment method must be set.");
        }

        $country = $this->contextProvider->getContext($sale)->getDeliveryCountry();

        $grid = $this->getGridForCountry($country);

        if (!isset($grid[$method->getId()])) {
            return null;
        }

        $weight = $sale->getShipmentWeight() ?? $sale->getWeightTotal();

        $resolvedPrice = new ResolvedShipmentPrice($method, $weight);

        if ($rule = $this->ruleRepository->findOneBySale($sale, $method)) {
            $price = $rule->getNetPrice();
        } else {
            $price = $this->resolvePrice($grid[$method->getId()], $weight);
        }

        return $resolvedPrice
            ->setPrice($price)
            ->setTaxes($this->getTaxesRates($method, $country));
    }

    /**
     * @inheritdoc
     */
    public function getPriceByCountryAndMethodAndWeight(
        CountryInterface $country,
        ShipmentMethodInterface $method,
        $weight
    ) {
        $grid = $this->getGridForCountry($country);

        if (!isset($grid[$method->getId()])) {
            return null;
        }

        $resolvedPrice = new ResolvedShipmentPrice($method, $weight);

        return $resolvedPrice
            ->setPrice($this->resolvePrice($grid[$method->getId()], $weight))
            ->setTaxes($this->getTaxesRates($method, $country));
    }

    /**
     * Entity manager clear event handler.
     */
    public function onClear(): void
    {
        $this->grids = [];
    }

    /**
     * Resolves the price form the given grid entry and total weight.
     *
     * @param array $entry
     * @param float $weight
     *
     * @return float
     */
    private function resolvePrice(array $entry, $weight)
    {
        $price = $count = 0;

        if ($weight > $entry['max_weight']) {
            $count = floor($weight / $entry['max_weight']);
            $weight = round(fmod($weight, $count), 3);
        }

        if (0 < $count) {
            $max = end($entry['prices'])['price'];
            $price = $count * $max;
        }

        foreach ($entry['prices'] as $p) {
            // If sale weight is lower than price weight
            if (1 === bccomp($p['weight'], $weight, 3)) {
                $price += $p['price'];
                break;
            }
        }

        return $price;
    }

    /**
     * Returns the price grid for the given country.
     *
     * @param CountryInterface $country
     *
     * @return array|mixed
     */
    private function getGridForCountry(CountryInterface $country)
    {
        if (isset($this->grids[$country->getId()])) {
            return $this->grids[$country->getId()];
        }

        $grid = [];

        $prices = $this->priceRepository->findByCountry($country);
        foreach ($prices as $price) {
            $method = $price->getMethod();

            // Create method if not exists
            if (!isset($grid[$method->getId()])) {
                $gateway = $this->gatewayRegistry->getGateway($method->getGatewayName());

                $grid[$method->getId()] = [
                    'method'     => $method,
                    'max_weight' => $gateway->getMaxWeight(),
                    'prices'     => [],
                ];
            }

            // Add price
            $grid[$method->getId()]['prices'][] = [
                'weight' => $price->getWeight(),
                'price'  => $price->getNetPrice(),
            ];
        }

        foreach ($grid as &$method) {
            // Sort prices by weight ASC
            usort($method['prices'], function ($a, $b) {
                if (0 === bccomp($a['weight'], $b['weight'], 3)) {
                    return 0;
                }

                return $a['weight'] > $b['weight'] ? 1 : -1;
            });

            // Fix max weight
            $max = end($method['prices'])['weight'];
            if (0 == $method['max_weight'] || $method['max_weight'] > $max) {
                $method['max_weight'] = $max;
            }

            unset($method);
        }

        return $this->grids[$country->getId()] = $grid;
    }

    /**
     * Returns the tax rates for the given method.
     *
     * @param ShipmentMethodInterface $method
     * @param CountryInterface        $country
     *
     * @return array
     */
    private function getTaxesRates(ShipmentMethodInterface $method, CountryInterface $country)
    {
        return array_map(function (TaxInterface $tax) {
            return $tax->getRate();
        }, $this->taxResolver->resolveTaxes($method, $country));
    }
}
