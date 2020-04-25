<?php

namespace Ekyna\Component\Commerce\Shipment\Resolver;

use Ekyna\Component\Commerce\Common\Context\ContextInterface;
use Ekyna\Component\Commerce\Common\Context\ContextProviderInterface;
use Ekyna\Component\Commerce\Common\Model\CountryInterface as Country;
use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Pricing\Model\TaxInterface;
use Ekyna\Component\Commerce\Pricing\Resolver\TaxResolverInterface;
use Ekyna\Component\Commerce\Shipment\Gateway\RegistryInterface;
use Ekyna\Component\Commerce\Shipment\Model\ResolvedShipmentPrice as Price;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentMethodInterface as Method;
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
    public function getAvailablePricesBySale(SaleInterface $sale, bool $availableOnly = true): array
    {
        $context = $this->contextProvider->getContext($sale);

        $prices = [];

        $grid = $this->getGridForCountry($context->getDeliveryCountry());

        $weight = $sale->getShipmentWeight() ?? $sale->getWeightTotal();

        foreach ($grid as $entry) {
            /** @var Method $method */
            $method = $entry['method'];
            if ($availableOnly && !$method->isAvailable()) {
                continue;
            }

            $resolvedPrice = new Price($method, $weight);

            if ($rule = $this->ruleRepository->findOneBySale($sale, $method)) {
                $price = $rule->getNetPrice();
            } else {
                $price = $this->resolvePrice($entry, $weight);
            }

            $resolvedPrice
                ->setPrice($price)
                ->setTaxes($this->getTaxesRates($method, $context));

            $prices[] = $resolvedPrice;
        }

        return $prices;
    }

    /**
     * @inheritdoc
     */
    public function getPriceBySale(SaleInterface $sale): ?Price
    {
        if (null === $method = $sale->getShipmentMethod()) {
            return null;
        }

        $context = $this->contextProvider->getContext($sale);

        $weight = $sale->getShipmentWeight() ?? $sale->getWeightTotal();

        $resolvedPrice = new Price($method, $weight);

        if ($rule = $this->ruleRepository->findOneBySale($sale, $method)) {
            $price = $rule->getNetPrice();
        } elseif ($grid = $this->getGridForCountryAndMethod($context->getDeliveryCountry(), $method)) {
            $price = $this->resolvePrice($grid, $weight);
        } else {
            return null;
        }

        return $resolvedPrice
            ->setPrice($price)
            ->setTaxes($this->getTaxesRates($method, $context));
    }

    /**
     * @inheritdoc
     */
    public function getPriceByCountryAndMethodAndWeight(Country $country, Method $method, float $weight): ?Price
    {
        if (!$grid = $this->getGridForCountryAndMethod($country, $method)) {
            return null;
        }

        return new Price(
            $method, $weight, $this->resolvePrice($grid, $weight)
        );
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
    private function resolvePrice(array $entry, float $weight): float
    {
        $price = $count = 0;

        if ($weight > $max = $entry['max_weight']) {
            $count = floor($weight / $max);
            $weight = round(fmod($weight, $max), 3);
        }

        if (0 < $count) {
            $max = end($entry['prices'])['price'];
            $price = $count * $max;
        }

        foreach ($entry['prices'] as $p) {
            // If sale weight is lower than or equal price weight
            if (-1 !== bccomp($p['weight'], $weight, 3)) {
                $price += $p['price'];
                break;
            }
        }

        return $price;
    }

    /**
     * Returns the price grid for the given country.
     *
     * @param Country $country
     *
     * @return array
     */
    private function getGridForCountry(Country $country): array
    {
        $this->loadCountryGrid($country);

        return $this->grids[$country->getId()];
    }

    /**
     * Returns the price grid for the given country and method.
     *
     * @param Country        $country
     * @param Method $method
     *
     * @return array|null
     */
    private function getGridForCountryAndMethod(Country $country, Method $method): ?array
    {
        $this->loadCountryGrid($country);

        if (!isset($this->grids[$country->getId()][$method->getId()])) {
            return null;
        }

        return $this->grids[$country->getId()][$method->getId()];
    }

    /**
     * Loads the country price grid.
     *
     * @param Country $country
     */
    private function loadCountryGrid(Country $country): void
    {
        if (isset($this->grids[$country->getId()])) {
            return;
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

        $this->grids[$country->getId()] = $grid;
    }

    /**
     * Returns the tax rates for the given method.
     *
     * @param Method $method
     * @param ContextInterface        $context
     *
     * @return array
     */
    private function getTaxesRates(Method $method, ContextInterface $context): array
    {
        return array_map(function (TaxInterface $tax) {
            return $tax->getRate();
        }, $this->taxResolver->resolveTaxes($method, $context));
    }
}
