<?php

namespace Ekyna\Component\Commerce\Tests\Pricing\Resolver;

use Ekyna\Component\Commerce\Pricing\Resolver\ResolvedTaxesCache;
use Ekyna\Component\Commerce\Pricing\Resolver\TaxResolver;
use Ekyna\Component\Commerce\Tests\Fixture;
use Ekyna\Component\Commerce\Tests\TestCase;

/**
 * Class TaxResolverTest
 * @package Ekyna\Component\Commerce\Tests\Pricing\Resolver
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class TaxResolverTest extends TestCase
{
    /**
     * @var TaxResolver
     */
    private $taxResolver;

    protected function setUp(): void
    {
        $this->taxResolver = new TaxResolver(
            $this->getCountryProviderMock(),
            $this->getWarehouseProviderMock(),
            $this->getTaxRuleRepositoryMock(),
        );
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->taxResolver = null;
    }

    /**
     * @param array $taxable
     * @param array $context
     * @param array $expected
     * @param array $taxRule
     * @param array $country
     * @param array $warehouse
     *
     * @dataProvider provide_resolveTaxes
     */
    public function test_it_resolve_taxes(
        array $taxable,
        array $context,
        array $expected,
        array $taxRule = null,
        array $country = null,
        array $warehouse = null
    ): void {
        if ($taxRule) {
            $this->configureTaxRuleRepository($taxRule);
        }

        if ($country) {
            $this->configureCountryProviderMock($country);
        }

        if ($warehouse) {
            $this->configureWarehouseProviderMock($warehouse);
        }

        $taxable = Fixture::subject($taxable);
        $context = Fixture::{$context['factory']}($context['data']);

        $expected = array_map(function ($tax) {
            return Fixture::tax($tax);
        }, $expected);

        $this->assertEquals($expected, $this->taxResolver->resolveTaxes($taxable, $context));
    }

    public function provide_resolveTaxes(): \Generator
    {
        yield 'Context unknown' => [
            'taxable'  => [
                'tax_group' => Fixture::TAX_GROUP_NORMAL,
            ],
            'context'  => [
                'factory' => 'context',
                'data'    => [],
            ],
            'result'   => [
                Fixture::TAX_FR_NORMAL,
            ],
            'tax_rule' => [
                'business' => false,
                'source'   => Fixture::COUNTRY_FR,
                'target'   => Fixture::COUNTRY_FR,
                'result'   => Fixture::TAX_RULE_FR_FR,
            ],
            'country'  => [
                'default' => Fixture::COUNTRY_FR,
                'current' => Fixture::COUNTRY_FR,
            ],
        ];

        yield 'Context FR -> FR' => [
            'taxable'  => [
                'tax_group' => Fixture::TAX_GROUP_REDUCED,
            ],
            'context'  => [
                'factory' => 'context',
                'data'    => [
                    'delivery_country' => Fixture::COUNTRY_FR,
                    'shipping_country' => Fixture::COUNTRY_FR,
                ],
            ],
            'result'   => [
                Fixture::TAX_FR_REDUCED,
            ],
            'tax_rule' => [
                'business' => false,
                'source'   => Fixture::COUNTRY_FR,
                'target'   => Fixture::COUNTRY_FR,
                'result'   => Fixture::TAX_RULE_FR_FR,
            ],
        ];

        yield 'Context FR -> ES' => [
            'taxable'  => [
                'tax_group' => Fixture::TAX_GROUP_SUPER_REDUCED,
            ],
            'context'  => [
                'factory' => 'context',
                'data'    => [
                    'delivery_country' => Fixture::COUNTRY_ES,
                    'shipping_country' => Fixture::COUNTRY_FR,
                ],
            ],
            'result'   => [
                Fixture::TAX_FR_SUPER_REDUCED,
            ],
            'tax_rule' => [
                'business' => false,
                'source'   => Fixture::COUNTRY_FR,
                'target'   => Fixture::COUNTRY_ES,
                'result'   => Fixture::TAX_RULE_FR_EU_B2C,
            ],
        ];

        yield 'Context FR -> US' => [
            'taxable'  => [
                'tax_group' => Fixture::TAX_GROUP_NORMAL,
            ],
            'context'  => [
                'factory' => 'context',
                'data'    => [
                    'delivery_country' => Fixture::COUNTRY_US,
                    'shipping_country' => Fixture::COUNTRY_FR,
                ],
            ],
            'result'   => [],
            'tax_rule' => [
                'business' => false,
                'source'   => Fixture::COUNTRY_FR,
                'target'   => Fixture::COUNTRY_US,
                'result'   => Fixture::TAX_RULE_FR_WORLD,
            ],
        ];

        yield 'Order Unknown' => [
            'taxable'   => [
                'tax_group' => Fixture::TAX_GROUP_INTERMEDIATE,
            ],
            'context'   => [
                'factory' => 'order',
                'data'    => [],
            ],
            'result'    => [
                Fixture::TAX_FR_INTERMEDIATE,
            ],
            'tax_rule'  => [
                'business' => false,
                'source'   => Fixture::COUNTRY_FR,
                'target'   => Fixture::COUNTRY_FR,
                'result'   => Fixture::TAX_RULE_FR_FR,
            ],
            'country'   => [
                'default' => Fixture::COUNTRY_FR,
                'current' => Fixture::COUNTRY_FR,
            ],
            'warehouse' => [
                null => [
                    'invocation' => $this->once(),
                    'country'    => Fixture::COUNTRY_FR,
                ],
            ],
        ];

        yield 'Order FR -> ES' => [
            'taxable'   => [
                'tax_group' => Fixture::TAX_GROUP_NORMAL,
            ],
            'context'   => [
                'factory' => 'order',
                'data'    => [
                    'delivery_address' => [
                        'country' => Fixture::COUNTRY_ES,
                    ],
                ],
            ],
            'result'    => [
                Fixture::TAX_FR_NORMAL,
            ],
            'tax_rule'  => [
                'business' => false,
                'source'   => Fixture::COUNTRY_FR,
                'target'   => Fixture::COUNTRY_ES,
                'result'   => Fixture::TAX_RULE_FR_EU_B2C,
            ],
            'country'   => null,
            'warehouse' => [
                Fixture::COUNTRY_ES => [
                    'invocation' => $this->once(),
                    'country'    => Fixture::COUNTRY_FR,
                ],
            ],
        ];

        yield 'Order FR -> ES (business)' => [
            'taxable'   => [
                'tax_group' => Fixture::TAX_GROUP_NORMAL,
            ],
            'context'   => [
                'factory' => 'order',
                'data'    => [
                    'vat_valid'        => true,
                    'vat_number'       => '123',
                    'delivery_address' => [
                        'country' => Fixture::COUNTRY_ES,
                    ],
                ],
            ],
            'result'    => [],
            'tax_rule'  => [
                'business' => true,
                'source'   => Fixture::COUNTRY_FR,
                'target'   => Fixture::COUNTRY_ES,
                'result'   => Fixture::TAX_RULE_FR_EU_B2B,
            ],
            'country'   => null,
            'warehouse' => [
                Fixture::COUNTRY_ES => [
                    'invocation' => $this->once(),
                    'country'    => Fixture::COUNTRY_FR,
                ],
            ],
        ];

        yield 'Supplier order FR -> FR' => [
            'taxable'  => [
                'tax_group' => Fixture::TAX_GROUP_REDUCED,
            ],
            'context'  => [
                'factory' => 'supplierOrder',
                'data'    => [
                    'supplier'  => [
                        'address' => [
                            'country' => Fixture::COUNTRY_FR,
                        ],
                    ],
                    'warehouse' => [
                        'country' => Fixture::COUNTRY_FR,
                    ],
                ],
            ],
            'result'   => [
                Fixture::TAX_FR_REDUCED,
            ],
            'tax_rule' => [
                'business' => true,
                'source'   => Fixture::COUNTRY_FR,
                'target'   => Fixture::COUNTRY_FR,
                'result'   => Fixture::TAX_RULE_FR_FR,
            ],
        ];

        yield 'Supplier order US -> FR' => [
            'taxable'  => [],
            'context'  => [
                'factory' => 'supplierOrder',
                'data'    => [
                    'supplier'  => [
                        'address' => [
                            'country' => Fixture::COUNTRY_US,
                        ],
                    ],
                    'warehouse' => [
                        'country' => Fixture::COUNTRY_FR,
                    ],
                ],
            ],
            'result'   => [],
            'tax_rule' => [
                'business' => true,
                'source'   => Fixture::COUNTRY_US,
                'target'   => Fixture::COUNTRY_FR,
                'result'   => null,
            ],
        ];

        yield 'Supplier order FR -> ES' => [
            'taxable'  => [
                'tax_group' => Fixture::TAX_GROUP_NORMAL,
            ],
            'context'  => [
                'factory' => 'supplierOrder',
                'data'    => [
                    'supplier'  => [
                        'address' => [
                            'country' => Fixture::COUNTRY_FR,
                        ],
                    ],
                    'warehouse' => [
                        'country' => Fixture::COUNTRY_ES,
                    ],
                ],
            ],
            'result'   => [],
            'tax_rule' => [
                'business' => true,
                'source'   => Fixture::COUNTRY_FR,
                'target'   => Fixture::COUNTRY_ES,
                'result'   => Fixture::TAX_RULE_FR_EU_B2B,
            ],
        ];
    }

    /**
     * @param array  $sale
     * @param array  $taxRule
     * @param array  $country
     * @param array  $warehouse
     * @param string $expected
     *
     * @dataProvider provide_resolveTaxRule
     */
    public function test_it_resolve_sale_tax_rule(
        string $expected,
        array $sale,
        array $taxRule,
        array $country = null,
        array $warehouse = null
    ): void {
        $sale = Fixture::order($sale);

        $this->configureTaxRuleRepository($taxRule);
        if ($country) {
            $this->configureCountryProviderMock($country);
        }
        if ($warehouse) {
            $this->configureWarehouseProviderMock($warehouse);
        }

        $this->assertSame(Fixture::taxRule($expected), $this->taxResolver->resolveSaleTaxRule($sale));
    }

    public function provide_resolveTaxRule(): \Generator
    {
        yield 'Unknown' => [
            'expected'  => Fixture::TAX_RULE_FR_FR,
            'sale'      => [],
            'tax_rule'  => [
                'business' => false,
                'source'   => Fixture::COUNTRY_FR,
                'target'   => Fixture::COUNTRY_FR,
                'result'   => Fixture::TAX_RULE_FR_FR,
            ],
            'country'   => [
                'default' => Fixture::COUNTRY_FR,
                'current' => Fixture::COUNTRY_FR,
            ],
            'warehouse' => [
                null => [
                    'invocation' => $this->once(),
                    'country'    => Fixture::COUNTRY_FR,
                ],
            ],
        ];

        yield 'FR -> ES' => [
            'expected'  => Fixture::TAX_RULE_FR_EU_B2C,
            'sale'      => [
                'delivery_address' => [
                    'country' => Fixture::COUNTRY_ES,
                ],
            ],
            'tax_rule'  => [
                'business' => false,
                'source'   => Fixture::COUNTRY_FR,
                'target'   => Fixture::COUNTRY_ES,
                'result'   => Fixture::TAX_RULE_FR_EU_B2C,
            ],
            'country'   => null,
            'warehouse' => [
                Fixture::COUNTRY_ES => [
                    'invocation' => $this->once(),
                    'country'    => Fixture::COUNTRY_FR,
                ],
            ],
        ];

        yield 'FR -> ES (business)' => [
            'expected'  => Fixture::TAX_RULE_FR_EU_B2B,
            'sale'      => [
                'vat_valid'        => true,
                'vat_number'       => '123',
                'delivery_address' => [
                    'country' => Fixture::COUNTRY_ES,
                ],
            ],
            'tax_rule'  => [
                'business' => true,
                'source'   => Fixture::COUNTRY_FR,
                'target'   => Fixture::COUNTRY_ES,
                'result'   => Fixture::TAX_RULE_FR_EU_B2B,
            ],
            'country'   => null,
            'warehouse' => [
                Fixture::COUNTRY_ES => [
                    'invocation' => $this->once(),
                    'country'    => Fixture::COUNTRY_FR,
                ],
            ],
        ];
    }

    public function test_it_use_cache(): void
    {
        $taxable = Fixture::subject([
            'tax_group' => Fixture::TAX_GROUP_NORMAL,
        ]);

        $context = Fixture::context([
            'shipping_country' => Fixture::COUNTRY_FR,
            'delivery_country' => Fixture::COUNTRY_FR,
        ]);

        $expected = [Fixture::tax(Fixture::TAX_FR_NORMAL)];

        $this->configureTaxRuleRepository([
            'business' => false,
            'source'   => Fixture::COUNTRY_FR,
            'target'   => Fixture::COUNTRY_FR,
            'result'   => Fixture::TAX_RULE_FR_FR,
        ]);

        $cache = $this->createMock(ResolvedTaxesCache::class);
        $this->taxResolver->setCache($cache);

        // It will try to retrieve the result from cache
        $cache->expects($this->at(0))->method('get')->willReturn(null);
        // It will set the result into cache
        $cache->expects($this->at(1))->method('set');
        // It will use the cache result (and won't use the tax repository)
        $cache->expects($this->at(2))->method('get')->willReturn($expected);

        $this->assertEquals($expected, $this->taxResolver->resolveTaxes($taxable, $context));
        $this->assertEquals($expected, $this->taxResolver->resolveTaxes($taxable, $context));
    }

    /**
     * Configures the tax rule repository.
     *
     * @param array $data
     */
    private function configureTaxRuleRepository(array $data): void
    {
        if ($data['business']) {
            $once = 'findOneForBusiness';
            $never = 'findOneForCustomer';
        } else {
            $once = 'findOneForCustomer';
            $never = 'findOneForBusiness';
        }

        $this
            ->getTaxRuleRepositoryMock()
            ->expects($this->once())
            ->method($once)
            ->with(Fixture::country($data['source']), Fixture::country($data['target']))
            ->willReturn($data['result'] ? Fixture::taxRule($data['result']) : null);

        $this
            ->getTaxRuleRepositoryMock()
            ->expects($this->never())
            ->method($never);
    }

    /**
     * Configures the country provider.
     *
     * @param array $data
     */
    private function configureCountryProviderMock(array $data): void
    {
        if ($data['default']) {
            $this
                ->getCountryProviderMock()
                ->method('getDefault')
                ->willReturn(Fixture::country($data['default']));
        }

        if ($data['current']) {
            $this
                ->getCountryProviderMock()
                ->method('getCountry')
                ->willReturn(Fixture::country($data['current']));
        }
    }

    /**
     * Configures the warehouse provider.
     *
     * @param array $data
     */
    private function configureWarehouseProviderMock(array $data): void
    {
        foreach ($data as $country => $warehouse) {
            if (isset($warehouse['invocation'])) {
                $invocation = $warehouse['invocation'];
                unset($warehouse['invocation']);
            } else {
                $invocation = $this->any();
            }

            $this
                ->getWarehouseProviderMock()
                ->expects($invocation)
                ->method('getWarehouse')
                ->with($country ? Fixture::country($country) : null)
                ->willReturn(Fixture::warehouse($warehouse));
        }
    }
}
