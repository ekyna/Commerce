<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Shipment\Resolver;

use Decimal\Decimal;
use Ekyna\Component\Commerce\Common\Context\ContextInterface;
use Ekyna\Component\Commerce\Common\Context\ContextProviderInterface;
use Ekyna\Component\Commerce\Common\Model\CountryInterface as Country;
use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Exception\LogicException;
use Ekyna\Component\Commerce\Pricing\Model\TaxInterface;
use Ekyna\Component\Commerce\Pricing\Resolver\TaxResolverInterface;
use Ekyna\Component\Commerce\Shipment\Gateway\GatewayRegistryInterface;
use Ekyna\Component\Commerce\Shipment\Gateway\Virtual\VirtualPlatform;
use Ekyna\Component\Commerce\Shipment\Model\ResolvedShipmentPrice as Price;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentMethodInterface as Method;
use Ekyna\Component\Commerce\Shipment\Repository\ShipmentMethodRepositoryInterface;
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
     * @var array<int, array<int, array{
     *     method: Method,
     *     max_weight: Decimal,
     *     prices: array<int, array{
     *         weight: Decimal,
     *         price: Decimal
     *     }
     * }>>
     */
    private array $grids = [];

    public function __construct(
        private readonly ShipmentPriceRepositoryInterface  $priceRepository,
        private readonly ShipmentRuleRepositoryInterface   $ruleRepository,
        private readonly ShipmentMethodRepositoryInterface $methodRepository,
        private readonly GatewayRegistryInterface          $gatewayRegistry,
        private readonly TaxResolverInterface              $taxResolver,
        private readonly ContextProviderInterface          $contextProvider
    ) {
    }

    public function getAvailablePricesBySale(SaleInterface $sale, bool $availableOnly = true): array
    {
        if (!$sale->hasPhysicalItem()) {
            if (null !== $price = $this->getNonPhysicalPrice()) {
                return [$price];
            }

            return [];
        }

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

    public function getPriceBySale(SaleInterface $sale): ?Price
    {
        if (!$sale->hasPhysicalItem()) {
            return $this->getNonPhysicalPrice();
        }

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

    public function getPriceByCountryAndMethodAndWeight(Country $country, Method $method, Decimal $weight): ?Price
    {
        if (!$grid = $this->getGridForCountryAndMethod($country, $method)) {
            return null;
        }

        return new Price(
            $method, $weight, $this->resolvePrice($grid, $weight)
        );
    }

    public function getNonPhysicalPrice(): ?Price
    {
        if (null === $method = $this->methodRepository->findOneByPlatform(VirtualPlatform::NAME)) {
            throw new LogicException('Failed to find shipment method using virtual platform');
        }

        return new Price($method, new Decimal(0), new Decimal(0));
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
     * @param array{
     *     method: Method,
     *     max_weight: Decimal,
     *     prices: array<int, array{
     *         weight: Decimal,
     *         price: Decimal
     *     }
     * } $entry
     */
    private function resolvePrice(array $entry, Decimal $weight): Decimal
    {
        $price = new Decimal(0);
        $count = 0;

        $max = $entry['max_weight'];
        if (0 < $max && $max < $weight) {
            $count = $weight->div($max)->floor();
            $weight = $weight->rem($max)->round(3);
        }

        if (0 < $count) {
            $max = end($entry['prices'])['price'];
            $price = $count * $max;
        }

        foreach ($entry['prices'] as $p) {
            // If sale weight is lower than or equal price weight
            if ($p['weight'] >= $weight) {
                $price += $p['price'];
                break;
            }
        }

        return $price;
    }

    /**
     * Returns the price grid for the given country.
     */
    private function getGridForCountry(Country $country): array
    {
        $this->loadCountryGrid($country);

        return $this->grids[$country->getId()];
    }

    /**
     * Returns the price grid for the given country and method.
     *
     * @return null|array<int, array{
     *     method: Method,
     *     max_weight: Decimal,
     *     prices: array<int, array{
     *         weight: Decimal,
     *         price: Decimal
     *     }
     * }>
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
                return $a['weight']->compareTo($b['weight']);
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
     */
    private function getTaxesRates(Method $method, ContextInterface $context): array
    {
        return array_map(function (TaxInterface $tax) {
            return $tax->getRate();
        }, $this->taxResolver->resolveTaxes($method, $context));
    }
}
