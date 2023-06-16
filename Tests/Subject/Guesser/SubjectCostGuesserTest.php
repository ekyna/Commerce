<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Tests\Subject\Guesser;

use Acme\Product\Entity\Product;
use Decimal\Decimal;
use Ekyna\Component\Commerce\Common\Model\CountryInterface;
use Ekyna\Component\Commerce\Stock\Model\StockUnitStates;
use Ekyna\Component\Commerce\Subject\Guesser\SubjectCostGuesser;
use Ekyna\Component\Commerce\Supplier\Calculator\SupplierOrderItemCalculatorInterface;
use Ekyna\Component\Commerce\Supplier\Model\SupplierOrderItemInterface;
use Ekyna\Component\Commerce\Supplier\Model\SupplierProductInterface;
use Ekyna\Component\Commerce\Tests\Fixture;
use Ekyna\Component\Commerce\Tests\TestCase;
use Ekyna\Component\Resource\Repository\RepositoryFactoryInterface;
use Generator;
use PHPUnit\Framework\MockObject\MockObject;

use function array_map;
use function is_null;

/**
 * Class SubjectCostGuesserTest
 * @package Ekyna\Component\Commerce\Tests\Subject\Guesser
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class SubjectCostGuesserTest extends TestCase
{
    private SupplierOrderItemCalculatorInterface|MockObject|null $itemCalculator;
    private SubjectCostGuesser|null                              $guesser;

    protected function setUp(): void
    {
        parent::setUp();

        $unitRepository = $this->getStockUnitRepositoryMock();
        $itemRepository = $this->getSupplierOrderItemRepositoryMock();
        $productRepository = $this->getSupplierProductRepositoryMock();
        $countryRepository = $this->getCountryRepositoryMock();

        $repositoryFactory = $this->createMock(RepositoryFactoryInterface::class);
        $repositoryFactory
            ->method('getRepository')
            ->willReturnMap([
                [Product::getStockUnitClass(), $unitRepository],
                [SupplierOrderItemInterface::class, $itemRepository],
                [SupplierProductInterface::class, $productRepository],
                [CountryInterface::class, $countryRepository],
            ]);

        $this->itemCalculator = $this->createMock(SupplierOrderItemCalculatorInterface::class);

        $this->guesser = new SubjectCostGuesser(
            $repositoryFactory,
            $this->itemCalculator,
            $this->getCurrencyConverter(),
        );
    }

    protected function tearDown(): void
    {
        $this->guesser = null;

        parent::tearDown();
    }

    /**
     * @param array|null $expected
     * @param array      $openedUnits
     * @param array      $closedUnits
     * @param array|null $item
     * @param array      $products
     *
     * @dataProvider provideGuess
     */
    public function testGuess(
        array $expected = null,
        array $openedUnits = [],
        array $closedUnits = [],
        array $item = null,
        array $products = []
    ): void {
        $subject = Fixture::subject();

        $this
            ->getStockUnitRepositoryMock()
            ->method('findLatestNotClosedBySubject')
            ->with($subject)
            ->willReturn(
                array_map(function (array $data) {
                    return Fixture::stockUnit($data);
                }, $openedUnits)
            );

        $this
            ->getStockUnitRepositoryMock()
            ->method('findLatestClosedBySubject')
            ->with($subject)
            ->willReturn(
                array_map(function (array $data) {
                    return Fixture::stockUnit($data);
                }, $closedUnits)
            );

        if (null !== $item) {
            $costs = $item;

            $item = Fixture::supplierOrderItem();

            $this
                ->itemCalculator
                ->method('calculateItemProductPrice')
                ->with($item)
                ->willReturn(new Decimal((string)$costs[0]));
            $this
                ->itemCalculator
                ->method('calculateItemShippingPrice')
                ->with($item)
                ->willReturn(new Decimal((string)$costs[1]));
        }

        $this
            ->getSupplierOrderItemRepositoryMock()
            ->method('findLatestOrderedBySubject')
            ->with($subject)
            ->willReturn($item);

        $this
            ->getSupplierProductRepositoryMock()
            ->method('findBySubject')
            ->with($subject)
            ->willReturn(
                array_map(function (array $data) {
                    return Fixture::supplierProduct($data);
                }, $products)
            );

        $result = $this->guesser->guess($subject);

        if (is_null($expected)) {
            self::assertNull($result);

            return;
        }

        self::assertEquals(new Decimal((string)$expected[0]), $result->getProduct());
        self::assertEquals(new Decimal((string)$expected[1]), $result->getSupply());
        self::assertEquals($expected[2], $result->isAverage());

        self::assertEquals(new Decimal(0), $result->getShipment());
    }

    public function provideGuess(): Generator
    {
        $supplier = Fixture::supplier([
            'address' => [],
        ]);

        yield 'No data available' => [
            'expected'    => null,
            'openedUnits' => [],
            'closedUnits' => [],
            'item'        => null,
            'products'    => [],
        ];

        yield 'Opened stock units' => [
            'expected'    => [49.50, 2.53, false],
            'openedUnits' => [
                [
                    'state'          => StockUnitStates::STATE_NEW,
                    'net_price'      => 0,
                    'shipping_price' => 0,
                ],
                [
                    'item'           => [
                        'order' => [
                            'supplier' => $supplier,
                        ],
                    ],
                    'state'          => StockUnitStates::STATE_READY,
                    'net_price'      => 49.50,
                    'shipping_price' => 2.53,
                ],
            ],
            'closedUnits' => [],
            'item'        => null,
            'products'    => [],
        ];

        yield 'Closed stock units' => [
            'expected'    => [32.25, 2.25, false],
            'openedUnits' => [],
            'closedUnits' => [
                [
                    'item'           => [
                        'order' => [
                            'supplier' => $supplier,
                        ],
                    ],
                    'state'          => StockUnitStates::STATE_CLOSED,
                    'net_price'      => 32.25,
                    'shipping_price' => 2.25,
                ],
            ],
            'item'        => null,
            'products'    => [],
        ];

        yield 'Supplier order item 1' => [
            'expected'    => [29.54, 2.34, true],
            'openedUnits' => [],
            'closedUnits' => [],
            'item'        => [29.54, 2.34],
            'products'    => [],
        ];

        yield 'Supplier order item 2' => [
            'expected'    => [29.54, 0, true],
            'openedUnits' => [],
            'closedUnits' => [],
            'item'        => [29.54, 0],
            'products'    => [],
        ];

        yield 'Supplier products 1' => [
            'expected'    => [36.37, 0, true],
            'openedUnits' => [],
            'closedUnits' => [],
            'item'        => null,
            'products'    => [
                [
                    'supplier' => [/* EUR */],
                    'price'    => 38.16,
                ],
                [
                    'supplier' => [/* EUR */],
                    'price'    => 34.58,
                ],
            ],
        ];

        yield 'Supplier products 2' => [
            'expected'    => [32.91, 0, true],
            'openedUnits' => [],
            'closedUnits' => [],
            'item'        => null,
            'products'    => [
                [
                    'supplier' => [/* EUR */],
                    'price'    => 38.16,
                ],
                [
                    'supplier' => ['currency' => Fixture::CURRENCY_USD],
                    'price'    => 34.58, // 27.66
                ],
            ],
        ];
    }
}
