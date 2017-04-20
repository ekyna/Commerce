<?php

namespace Ekyna\Component\Commerce\Tests\Shipment\Resolver;

use Ekyna\Component\Commerce\Common\Model\CountryInterface;
use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Shipment\Gateway\GatewayInterface;
use Ekyna\Component\Commerce\Shipment\Gateway\GatewayRegistryInterface;
use Ekyna\Component\Commerce\Shipment\Model\ResolvedShipmentPrice;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentMethodInterface;
use Ekyna\Component\Commerce\Shipment\Repository\ShipmentPriceRepositoryInterface;
use Ekyna\Component\Commerce\Shipment\Repository\ShipmentRuleRepositoryInterface;
use Ekyna\Component\Commerce\Shipment\Resolver\ShipmentPriceResolver;
use Ekyna\Component\Commerce\Tests\Fixture;
use Ekyna\Component\Commerce\Tests\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Class ShipmentPriceResolverTest
 * @package Ekyna\Component\Commerce\Tests\Shipment\Resolver
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class ShipmentPriceResolverTest extends TestCase
{
    /**
     * @var ShipmentPriceResolver
     */
    private $priceResolver;

    /**
     * @var ShipmentPriceRepositoryInterface|MockObject
     */
    private $priceRepository;

    /**
     * @var ShipmentRuleRepositoryInterface|MockObject
     */
    private $ruleRepository;

    /**
     * @var GatewayRegistryInterface|MockObject
     */
    private $gatewayRegistry;

    protected function setUp(): void
    {
        $this->priceRepository = $this->createMock(ShipmentPriceRepositoryInterface::class);
        $this->ruleRepository = $this->createMock(ShipmentRuleRepositoryInterface::class);
        $this->gatewayRegistry = $this->createMock(GatewayRegistryInterface::class);

        $this->priceResolver = new ShipmentPriceResolver(
            $this->priceRepository,
            $this->ruleRepository,
            $this->gatewayRegistry,
            $this->getTaxResolverMock(),
            $this->getContextProviderMock()
        );
    }

    protected function tearDown(): void
    {
        parent:: tearDown();

        $this->priceResolver = null;
        $this->gatewayRegistry = null;
        $this->ruleRepository = null;
        $this->priceRepository = null;
    }

    /**
     * @param array $order    The sale
     * @param array $context  The context
     * @param array $rules    The rule repository config
     * @param array $prices   The price resolver config
     * @param array $gateways The gateway registry config
     * @param array $taxes    The tax resolver registry config
     * @param bool $available Whether to resolve available methods prices
     * @param array $expected The expected resolved price
     *
     * @dataProvider provide_getAvailablePricesBySale
     */
    public function test_getAvailablePricesBySale(
        array $order,
        array $context,
        array $rules,
        array $prices,
        array $gateways,
        array $taxes,
        bool $available,
        array $expected
    ): void {
        $order = Fixture::order($order);
        $context = Fixture::context($context);

        $this
            ->getContextProviderMock()
            ->method('getContext')
            ->with($order)
            ->willReturn($context);

        $this->configureRuleRepository($rules);
        $this->configurePriceResolver($prices);
        $this->configureGatewayRegistry($gateways);
        $this->configureTaxResolver($taxes);

        $expected = array_map(function($price) {
            if (null !== $price) {
                $taxes = $price['taxes'] ?? [];
                $price = new ResolvedShipmentPrice(
                    Fixture::shipmentMethod($price['method']),
                    $price['weight'],
                    $price['price']
                );
                $price->setTaxes($taxes);
            }

            return $price;
        }, $expected);

        $this->assertEquals(
            $expected,
            $this->priceResolver->getAvailablePricesBySale($order, $available)
        );
    }

    public function provide_getAvailablePricesBySale(): \Generator
    {
        yield 'No shipment method, FR -> FR, all' => [
            'sale'     => [
                'shipment_method' => null,
                'shipment_weight' => null,
                'weight_total'    => 9.,
            ],
            'context'  => [
                'delivery_country' => Fixture::COUNTRY_FR,
                'shipping_country' => Fixture::COUNTRY_FR,
            ],
            'rules'    => [],
            'prices'   => [
                Fixture::COUNTRY_FR => [
                    'shipment_price_UPS_FR_1',
                    'shipment_price_UPS_FR_2',
                    'shipment_price_UPS_FR_3',
                    'shipment_price_UPS_FR_4',
                    'shipment_price_DHL_FR_1',
                    'shipment_price_DHL_FR_2',
                    'shipment_price_DHL_FR_3',
                    'shipment_price_DHL_FR_4',
                ],
            ],
            'gateways' => [
                Fixture::SHIPMENT_METHOD_UPS => ['max_weight' => 30.,],
                Fixture::SHIPMENT_METHOD_DHL => ['max_weight' => 24.,],
            ],
            'taxes'    => [
                Fixture::SHIPMENT_METHOD_UPS => [Fixture::TAX_FR_NORMAL],
                Fixture::SHIPMENT_METHOD_DHL => [Fixture::TAX_FR_NORMAL],
            ],
            'available' => false,
            'expected' => [
                [
                    'method' => Fixture::SHIPMENT_METHOD_UPS,
                    'weight' => 9.,
                    'price'  => 11.,
                    'taxes'  => [20],
                ],
                [
                    'method' => Fixture::SHIPMENT_METHOD_DHL,
                    'weight' => 9.,
                    'price'  => 12.,
                    'taxes'  => [20],
                ],
            ],
        ];

        yield 'No shipment method, FR -> FR, available only' => [
            'sale'     => [
                'shipment_method' => null,
                'shipment_weight' => null,
                'weight_total'    => 18.,
            ],
            'context'  => [
                'delivery_country' => Fixture::COUNTRY_FR,
                'shipping_country' => Fixture::COUNTRY_FR,
            ],
            'rules'    => [],
            'prices'   => [
                Fixture::COUNTRY_FR => [
                    'shipment_price_UPS_FR_1',
                    'shipment_price_UPS_FR_2',
                    'shipment_price_UPS_FR_3',
                    'shipment_price_UPS_FR_4',
                    'shipment_price_DHL_FR_1',
                    'shipment_price_DHL_FR_2',
                    'shipment_price_DHL_FR_3',
                    'shipment_price_DHL_FR_4',
                ],
            ],
            'gateways' => [
                Fixture::SHIPMENT_METHOD_UPS => ['max_weight' => 30.,],
                Fixture::SHIPMENT_METHOD_DHL => ['max_weight' => 24.,],
            ],
            'taxes'    => [
                Fixture::SHIPMENT_METHOD_UPS => [Fixture::TAX_FR_NORMAL],
                Fixture::SHIPMENT_METHOD_DHL => [Fixture::TAX_FR_NORMAL],
            ],
            'available' => true,
            'expected' => [
                [
                    'method' => Fixture::SHIPMENT_METHOD_UPS,
                    'weight' => 18.,
                    'price'  => 16.,
                    'taxes'  => [20],
                ],
            ],
        ];
    }

    /**
     * @param array      $order    The sale
     * @param array      $context  The context
     * @param array      $rules    The rule repository config
     * @param array      $prices   The price resolver config
     * @param array      $gateways The gateway registry config
     * @param array      $taxes    The tax resolver registry config
     * @param array|null $expected The expected resolved price
     *
     * @dataProvider provide_getPriceBySale
     */
    public function test_getPriceBySale(
        array $order,
        array $context,
        array $rules,
        array $prices,
        array $gateways,
        array $taxes,
        array $expected = null
    ): void {
        $order = Fixture::order($order);
        $context = Fixture::context($context);

        $this
            ->getContextProviderMock()
            ->method('getContext')
            ->with($order)
            ->willReturn($context);

        $this->configureRuleRepository($rules);
        $this->configurePriceResolver($prices);
        $this->configureGatewayRegistry($gateways);
        $this->configureTaxResolver($taxes);

        if (null !== $expected) {
            $taxes = $expected['taxes'] ?? [];
            $expected = new ResolvedShipmentPrice(
                Fixture::shipmentMethod($expected['method']),
                $expected['weight'],
                $expected['price']
            );
            $expected->setTaxes($taxes);
        }

        $this->assertEquals($expected, $this->priceResolver->getPriceBySale($order));
    }

    public function provide_getPriceBySale(): \Generator
    {
        yield 'No shipment method' => [
            'sale'     => [
                'shipment_method' => null,
                'shipment_weight' => null,
                'weight_total'    => 14.,
            ],
            'context'  => [],
            'rules'    => [],
            'prices'   => [],
            'gateways' => [],
            'taxes'    => [],
            'expected' => null,
        ];

        yield 'UPS, FR -> FR (tax)' => [
            'sale'     => [
                'shipment_method' => Fixture::SHIPMENT_METHOD_UPS,
                'shipment_weight' => null,
                'weight_total'    => 14.,
            ],
            'context'  => [
                'delivery_country' => Fixture::COUNTRY_FR,
                'shipping_country' => Fixture::COUNTRY_FR,
            ],
            'rules'    => [],
            'prices'   => [
                Fixture::COUNTRY_FR => [
                    'shipment_price_UPS_FR_1',
                    'shipment_price_UPS_FR_2',
                    'shipment_price_UPS_FR_3',
                    'shipment_price_UPS_FR_4',
                ],
            ],
            'gateways' => [
                Fixture::SHIPMENT_METHOD_UPS => [
                    'max_weight' => 30.,
                ],
            ],
            'taxes'    => [
                Fixture::SHIPMENT_METHOD_UPS => [Fixture::TAX_FR_NORMAL],
            ],
            'expected' => [
                'method' => Fixture::SHIPMENT_METHOD_UPS,
                'weight' => 14.,
                'price'  => 16.,
                'taxes'  => [20],
            ],
        ];

        yield 'UPS, FR -> FR (shipment weight > max)' => [
            'sale'     => [
                'shipment_method' => Fixture::SHIPMENT_METHOD_UPS,
                'shipment_weight' => 70,
                'weight_total'    => 19.,
            ],
            'context'  => [
                'delivery_country' => Fixture::COUNTRY_FR,
                'shipping_country' => Fixture::COUNTRY_FR,
            ],
            'rules'    => [],
            'prices'   => [
                Fixture::COUNTRY_FR => [
                    'shipment_price_UPS_FR_1',
                    'shipment_price_UPS_FR_2',
                    'shipment_price_UPS_FR_3',
                    'shipment_price_UPS_FR_4',
                ],
            ],
            'gateways' => [
                Fixture::SHIPMENT_METHOD_UPS => [
                    'max_weight' => 30.,
                ],
            ],
            'taxes'    => [
                Fixture::SHIPMENT_METHOD_UPS => [Fixture::TAX_FR_NORMAL],
            ],
            'expected' => [
                'method' => Fixture::SHIPMENT_METHOD_UPS,
                'weight' => 70.,
                'price'  => 49., // (19 [30kg] * 2) + 11 [10kg]
                'taxes'  => [20],
            ],
        ];

        yield 'UPS, FR -> FR (with rule)' => [
            'sale'     => [
                '_reference'      => 'test_sale',
                'shipment_method' => Fixture::SHIPMENT_METHOD_UPS,
                'shipment_weight' => 14,
                'weight_total'    => 10.,
            ],
            'context'  => [
                'delivery_country' => Fixture::COUNTRY_FR,
                'shipping_country' => Fixture::COUNTRY_FR,
            ],
            'rules'    => [
                [
                    'sale'   => 'test_sale',
                    'method' => Fixture::SHIPMENT_METHOD_UPS,
                    'rule'   => ['price' => 9.9,],
                ],
            ],
            'prices'   => [
                Fixture::COUNTRY_FR => [
                    'shipment_price_UPS_FR_1',
                    'shipment_price_UPS_FR_2',
                    'shipment_price_UPS_FR_3',
                    'shipment_price_UPS_FR_4',
                ],
            ],
            'gateways' => [
                Fixture::SHIPMENT_METHOD_UPS => [
                    'max_weight' => 30.,
                ],
            ],
            'taxes'    => [
                Fixture::SHIPMENT_METHOD_UPS => [Fixture::TAX_FR_NORMAL],
            ],
            'expected' => [
                'method' => Fixture::SHIPMENT_METHOD_UPS,
                'weight' => 14.,
                'price'  => 9.9, // from rule
                'taxes'  => [20],
            ],
        ];

        yield 'DHL, FR -> US (no tax)' => [
            'sale'     => [
                'shipment_method' => Fixture::SHIPMENT_METHOD_DHL,
                'shipment_weight' => null,
                'weight_total'    => 14.,
            ],
            'context'  => [
                'delivery_country' => Fixture::COUNTRY_US,
                'shipping_country' => Fixture::COUNTRY_FR,
            ],
            'rules'    => [],
            'prices'   => [
                Fixture::COUNTRY_US => [
                    'shipment_price_DHL_US_1',
                    'shipment_price_DHL_US_2',
                    'shipment_price_DHL_US_3',
                    'shipment_price_DHL_US_4',
                ],
            ],
            'gateways' => [
                Fixture::SHIPMENT_METHOD_DHL => ['max_weight' => 20.,],
            ],
            'taxes'    => [],
            'expected' => [
                'method' => Fixture::SHIPMENT_METHOD_DHL,
                'weight' => 14.,
                'price'  => 20.,
            ],
        ];

        yield 'DHL, FR -> US (shipment weight > max)' => [
            'sale'     => [
                'shipment_method' => Fixture::SHIPMENT_METHOD_DHL,
                'shipment_weight' => 79,
                'weight_total'    => 50.,
            ],
            'context'  => [
                'delivery_country' => Fixture::COUNTRY_US,
                'shipping_country' => Fixture::COUNTRY_FR,
            ],
            'rules'    => [],
            'prices'   => [
                Fixture::COUNTRY_US => [
                    'shipment_price_DHL_US_1',
                    'shipment_price_DHL_US_2',
                    'shipment_price_DHL_US_3',
                    'shipment_price_DHL_US_4',
                ],
            ],
            'gateways' => [
                Fixture::SHIPMENT_METHOD_DHL => ['max_weight' => 30.,],
            ],
            'taxes'    => [],
            'expected' => [
                'method' => Fixture::SHIPMENT_METHOD_DHL,
                'weight' => 79.,
                'price'  => 88., // (24 [24kg] * 3) + 16 [8kg]
            ],
        ];
    }

    /**
     * @param string $country  The country reference
     * @param string $method   The method reference
     * @param float  $weight   The weight
     * @param array  $prices   The price repository config
     * @param array  $gateways The gateway registry config
     * @param float  $expected The expected resolved price
     *
     * @dataProvider provider_getPriceByCountryAndMethodAndWeight
     */
    public function test_getPriceByCountryAndMethodAndWeight(
        string $country,
        string $method,
        float $weight,
        array $prices,
        array $gateways,
        float $expected = null
    ): void {
        $this->configurePriceResolver($prices);
        $this->configureGatewayRegistry($gateways);

        $country = Fixture::country($country);
        $method = Fixture::shipmentMethod($method);

        $actual = $this
            ->priceResolver
            ->getPriceByCountryAndMethodAndWeight($country, $method, $weight);

        if (null !== $expected) {
            $expected = new ResolvedShipmentPrice($method, $weight, $expected);
        }

        $this->assertEquals($expected, $actual);
    }

    public function provider_getPriceByCountryAndMethodAndWeight(): \Generator
    {
        // TODO null case

        yield [
            'country'  => Fixture::COUNTRY_FR,
            'method'   => Fixture::SHIPMENT_METHOD_UPS,
            'weight'   => 12.,
            'prices'   => [
                Fixture::COUNTRY_FR => [
                    'shipment_price_UPS_FR_1',
                    'shipment_price_UPS_FR_2',
                    'shipment_price_UPS_FR_3',
                    'shipment_price_UPS_FR_4',
                ],
            ],
            'gateways' => [
                Fixture::SHIPMENT_METHOD_UPS => [
                    'max_weight' => 30.,
                ],
            ],
            'expected' => 16.,
        ];

        yield [
            'country'  => Fixture::COUNTRY_US,
            'method'   => Fixture::SHIPMENT_METHOD_DHL,
            'weight'   => 12.,
            'prices'   => [
                Fixture::COUNTRY_US => [
                    'shipment_price_DHL_US_1',
                    'shipment_price_DHL_US_2',
                    'shipment_price_DHL_US_3',
                    'shipment_price_DHL_US_4',
                ],
            ],
            'gateways' => [
                Fixture::SHIPMENT_METHOD_DHL => [
                    'max_weight' => 20.,
                ],
            ],
            'expected' => 20.,
        ];
    }

    private function configureRuleRepository(array $rules): void
    {
        if (empty($rules)) {
            return;
        }

        $key = function (SaleInterface $sale, ShipmentMethodInterface $method) {
            return $sale->getId() . '-' . $method->getId();
        };

        $map = [];
        foreach ($rules as $config) {
            $sale = Fixture::order($config['sale']);
            $method = Fixture::shipmentMethod($config['method']);
            $map[$key($sale, $method)] = $config['rule'] ? Fixture::shipmentRule($config['rule']) : null;
        }

        $this
            ->ruleRepository
            ->method('findOneBySale')
            ->willReturnCallback(function (SaleInterface $sale, ShipmentMethodInterface $method) use ($key, $map) {
                $k = $key($sale, $method);

                if (isset($map[$k])) {
                    return $map[$k];
                }

                return null;
            });
    }

    private function configurePriceResolver(array $prices): void
    {
        if (empty($prices)) {
            return;
        }

        $map = [];
        foreach ($prices as $country => $references) {
            $map[$country] = array_map(function ($reference) {
                return Fixture::shipmentPrice($reference);
            }, $references);
        }

        $this
            ->priceRepository
            ->method('findByCountry')
            ->willReturnCallback(function (CountryInterface $country) use ($map) {
                return $map[$country->getCode()];
            });
    }

    private function configureGatewayRegistry(array $gateways): void
    {
        if (empty($gateways)) {
            return;
        }

        $map = [];
        foreach ($gateways as $method => $config) {
            $method = Fixture::shipmentMethod($method);

            $gateway = $this->createMock(GatewayInterface::class);
            $gateway
                ->method('getMaxWeight')
                ->willReturn($config['max_weight']);

            $map[$method->getGatewayName()] = $gateway;
        }

        $this
            ->gatewayRegistry
            ->method('getGateway')
            ->willReturnCallback(function ($name) use ($map) {
                return $map[$name];
            });
    }

    private function configureTaxResolver(array $config): void
    {
        $taxMap = [];
        foreach ($config as $method => $taxes) {
            $taxMap[Fixture::shipmentMethod($method)->getId()] = array_map(function (string $tax) {
                return Fixture::tax($tax);
            }, $taxes);
        }

        $this
            ->getTaxResolverMock()
            ->method('resolveTaxes')
            ->willReturnCallback(function (ShipmentMethodInterface $method) use ($taxMap) {
                if (isset($taxMap[$method->getId()])) {
                    return $taxMap[$method->getId()];
                }

                return [];
            });
    }
}
