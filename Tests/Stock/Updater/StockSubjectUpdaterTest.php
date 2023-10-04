<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Tests\Stock\Updater;

use Acme\Product\Entity\Product;
use DateTime;
use Decimal\Decimal;
use Ekyna\Component\Commerce\Stock\Model\StockComponent;
use Ekyna\Component\Commerce\Stock\Model\StockSubjectModes;
use Ekyna\Component\Commerce\Stock\Model\StockSubjectStates;
use Ekyna\Component\Commerce\Stock\Model\StockUnitStates;
use Ekyna\Component\Commerce\Stock\Resolver\StockUnitResolverInterface;
use Ekyna\Component\Commerce\Stock\Updater\StockSubjectUpdater;
use Ekyna\Component\Commerce\Supplier\Repository\SupplierProductRepositoryInterface;
use Ekyna\Component\Commerce\Tests\Fixture;
use Generator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Class StockSubjectUpdaterTest
 * @package Ekyna\Component\Commerce\Tests\Stock\Updater
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class StockSubjectUpdaterTest extends TestCase
{
    private StockUnitResolverInterface|MockObject|null         $stockUnitResolver;
    private SupplierProductRepositoryInterface|MockObject|null $supplierProductRepository;
    private StockSubjectUpdater|null                           $updater;

    protected function setUp(): void
    {
        $this->stockUnitResolver = $this->createMock(StockUnitResolverInterface::class);
        $this->supplierProductRepository = $this->createMock(SupplierProductRepositoryInterface::class);
        $this->updater = new StockSubjectUpdater($this->stockUnitResolver, $this->supplierProductRepository);
    }

    protected function tearDown(): void
    {
        $this->updater = null;
        $this->supplierProductRepository = null;
        $this->stockUnitResolver = null;
    }

    /**
     * @dataProvider provideUpdate
     */
    public function testUpdate(
        array $result,
        Product $subject,
        array $units = [],
        DateTime $eda = null,
        string|int $available = 0
    ): void {
        $this
            ->stockUnitResolver
            ->expects(self::any())
            ->method('findNotClosed')
            ->with($subject)
            ->willReturn($units);

        $this
            ->supplierProductRepository
            ->expects(self::any())
            ->method('getMinEstimatedDateOfArrivalBySubject')
            ->with($subject)
            ->willReturn($eda);

        $this
            ->supplierProductRepository
            ->expects(self::any())
            ->method('getAvailableQuantitySumBySubject')
            ->with($subject)
            ->willReturn(new Decimal($available));

        $this
            ->supplierProductRepository
            ->expects(self::any())
            ->method('getOrderedQuantitySumBySubject')
            ->with($subject)
            ->willReturn(new Decimal(0));

        $this->updater->update($subject);

        $result = array_replace([
            'mode'      => null,
            'state'     => null,
            'in'        => 0,
            'available' => 0,
            'virtual'   => 0,
            'eda'       => null,
        ], $result);

        self::assertEquals($result['mode'], $subject->getStockMode());
        self::assertEquals($result['state'], $subject->getStockState());
        self::assertEquals(new Decimal($result['in']), $subject->getInStock());
        self::assertEquals(new Decimal($result['available']), $subject->getAvailableStock());
        self::assertEquals(new Decimal($result['virtual']), $subject->getVirtualStock());
        self::assertEquals($result['eda'], $subject->getEstimatedDateOfArrival());
    }

    public function provideUpdate(): Generator
    {
        $subject = Fixture::subject(['mode' => StockSubjectModes::MODE_DISABLED]);
        yield 'Simple 1' => [
            [
                'mode'  => StockSubjectModes::MODE_DISABLED,
                'state' => StockSubjectStates::STATE_IN_STOCK,
            ],
            $subject,
        ];

        $subject = Fixture::subject(['mode' => StockSubjectModes::MODE_AUTO]);
        yield 'Simple 2' => [
            [
                'mode'    => StockSubjectModes::MODE_AUTO,
                'state'   => StockSubjectStates::STATE_OUT_OF_STOCK,
                'virtual' => -10,
            ],
            $subject,
            [
                Fixture::stockUnit([
                    'state' => StockUnitStates::STATE_NEW,
                    'sold'  => 10,
                ]),
            ],
        ];

        $subject = Fixture::subject(['mode' => StockSubjectModes::MODE_AUTO]);
        yield 'Simple 3' => [
            [
                'mode'  => StockSubjectModes::MODE_AUTO,
                'state' => StockSubjectStates::STATE_OUT_OF_STOCK,
                'in'    => 5,
                'eda'   => null,
            ],
            $subject,
            [
                Fixture::stockUnit([
                    'state'    => StockUnitStates::STATE_READY,
                    'sold'     => 20,
                    'shipped'  => 10,
                    'ordered'  => 20,
                    'received' => 15,
                    'eda'      => new DateTime('+1 day'),
                ]),
            ],
        ];

        $subject = Fixture::subject(['mode' => StockSubjectModes::MODE_AUTO]);
        yield 'Simple 4' => [
            [
                'mode'      => StockSubjectModes::MODE_AUTO,
                'state'     => StockSubjectStates::STATE_IN_STOCK,
                'in'        => 10,
                'available' => 10,
                'virtual'   => 10,
                'eda'       => null,
            ],
            $subject,
            [
                Fixture::stockUnit([
                    'state'    => StockUnitStates::STATE_READY,
                    'sold'     => 10,
                    'shipped'  => 10,
                    'ordered'  => 20,
                    'received' => 20,
                ]),
            ],
        ];

        $subject = Fixture::subject(['mode' => StockSubjectModes::MODE_AUTO]);
        $eda = new DateTime('+1 day');
        yield 'Simple 5' => [
            [
                'mode'    => StockSubjectModes::MODE_AUTO,
                'state'   => StockSubjectStates::STATE_PRE_ORDER,
                'in'      => 5,
                'virtual' => 5,
                'eda'     => $eda,
            ],
            $subject,
            [
                Fixture::stockUnit([
                    'state'    => StockUnitStates::STATE_READY,
                    'sold'     => 15,
                    'shipped'  => 10,
                    'ordered'  => 20,
                    'received' => 15,
                    'eda'      => $eda,
                ]),
                Fixture::stockUnit([
                    'state'   => StockUnitStates::STATE_PENDING,
                    'sold'    => 20,
                    'ordered' => 20,
                    'eda'     => new DateTime('+2 day'),
                ]),
            ],
        ];

        // EDA from supplier data
        $subject = Fixture::subject(['mode' => StockSubjectModes::MODE_AUTO]);
        $eda = new DateTime('+2 day');
        yield 'Simple 6' => [
            [
                'mode'  => StockSubjectModes::MODE_AUTO,
                'state' => StockSubjectStates::STATE_PRE_ORDER,
                'eda'   => $eda,
            ],
            $subject,
            [],
            $eda,
            10,
        ];
        // TODO with eda and ordered stock

        $subject = Fixture::subject(['mode' => StockSubjectModes::MODE_JUST_IN_TIME]);
        yield 'Simple 7' => [
            [
                'mode'  => StockSubjectModes::MODE_JUST_IN_TIME,
                'state' => StockSubjectStates::STATE_PRE_ORDER,
            ],
            $subject,
            [],
        ];

        $subject = Fixture::subject(['mode' => StockSubjectModes::MODE_JUST_IN_TIME]);
        $eda = new DateTime('+1 day');
        yield 'Simple 8' => [
            [
                'mode'    => StockSubjectModes::MODE_JUST_IN_TIME,
                'state'   => StockSubjectStates::STATE_IN_STOCK,
                'virtual' => 20,
                'eda'     => $eda,
            ],
            $subject,
            [
                Fixture::stockUnit([
                    'state'   => StockUnitStates::STATE_PENDING,
                    'ordered' => 20,
                    'eda'     => $eda,
                ]),
            ],
        ];

        $subject = Fixture::subject(['mode' => StockSubjectModes::MODE_JUST_IN_TIME]);
        $eda = new DateTime('+15 day');
        yield 'Simple 9' => [
            [
                'mode'    => StockSubjectModes::MODE_JUST_IN_TIME,
                'state'   => StockSubjectStates::STATE_IN_STOCK,
                'in'      => 10,
                'virtual' => 50,
                'eda'     => $eda,
            ],
            $subject,
            [
                Fixture::stockUnit([
                    'state'    => StockUnitStates::STATE_PENDING,
                    'sold'     => 30,
                    'adjusted' => 10,
                    'ordered'  => 20,
                    'eda'      => new DateTime('+5 day'),
                ]),
                Fixture::stockUnit([
                    'state'   => StockUnitStates::STATE_PENDING,
                    'sold'    => 50,
                    'ordered' => 50,
                    'eda'     => new DateTime('+10 day'),
                ]),
                Fixture::stockUnit([
                    'state'   => StockUnitStates::STATE_PENDING,
                    'sold'    => 20,
                    'ordered' => 50,
                    'eda'     => $eda,
                ]),
                Fixture::stockUnit([
                    'state'   => StockUnitStates::STATE_PENDING,
                    'ordered' => 20,
                    'eda'     => new DateTime('+20 day'),
                ]),
            ],
        ];

        // ---------------------------------------- COMPOUND ----------------------------------------

        $subject = Fixture::subject(['mode' => StockSubjectModes::MODE_DISABLED]);
        $subject->setStockCompound(true);
        $subject->setStockComposition([
            new StockComponent(
                Fixture::subject(),
                new Decimal(1)
            ),
        ]);

        yield 'Compound 1' => [
            [
                'mode'  => StockSubjectModes::MODE_DISABLED,
                'state' => StockSubjectStates::STATE_IN_STOCK,
            ],
            $subject,
        ];

        $subject = Fixture::subject(['mode' => StockSubjectModes::MODE_DISABLED]);
        $subject->setStockCompound(true);
        $subject->setStockComposition([
            new StockComponent(
                Fixture::subject([
                    'mode'      => StockSubjectModes::MODE_AUTO,
                    'state'     => StockSubjectStates::STATE_IN_STOCK,
                    'in'        => 20,
                    'available' => 20,
                ]),
                new Decimal(1)
            ),
            new StockComponent(
                Fixture::subject([
                    'mode'      => StockSubjectModes::MODE_AUTO,
                    'state'     => StockSubjectStates::STATE_IN_STOCK,
                    'in'        => 20,
                    'available' => 20,
                ]),
                new Decimal(2)
            ),
        ]);

        yield 'Compound 2' => [
            [
                'mode'      => StockSubjectModes::MODE_AUTO,
                'state'     => StockSubjectStates::STATE_IN_STOCK,
                'in'        => 10,
                'available' => 10,
            ],
            $subject,
        ];

        $subject = Fixture::subject(['mode' => StockSubjectModes::MODE_DISABLED]);
        $subject->setStockCompound(true);
        $subject->setStockComposition([
            new StockComponent(
                Fixture::subject([
                    'mode'  => StockSubjectModes::MODE_DISABLED,
                    'state' => StockSubjectStates::STATE_IN_STOCK,
                ]),
                new Decimal(1)
            ),
            new StockComponent(
                Fixture::subject([
                    'mode'      => StockSubjectModes::MODE_AUTO,
                    'state'     => StockSubjectStates::STATE_IN_STOCK,
                    'in'        => 30,
                    'available' => 30,
                ]),
                new Decimal(3)
            ),
        ]);

        yield 'Compound 3' => [
            [
                'mode'      => StockSubjectModes::MODE_AUTO,
                'state'     => StockSubjectStates::STATE_IN_STOCK,
                'in'        => 10,
                'available' => 10,
            ],
            $subject,
        ];

        $subject = Fixture::subject(['mode' => StockSubjectModes::MODE_DISABLED]);
        $subject->setStockCompound(true);
        $subject->setStockComposition([
            new StockComponent(
                Fixture::subject([
                    'mode'    => StockSubjectModes::MODE_JUST_IN_TIME,
                    'state'   => StockSubjectStates::STATE_PRE_ORDER,
                    'virtual' => 20,
                ]),
                new Decimal(1)
            ),
            new StockComponent(
                Fixture::subject([
                    'mode'    => StockSubjectModes::MODE_JUST_IN_TIME,
                    'state'   => StockSubjectStates::STATE_IN_STOCK,
                    'virtual' => 30,
                    'eda'     => new DateTime('+3 days'),
                ]),
                new Decimal(3)
            ),
        ]);

        yield 'Compound 4' => [
            [
                'mode'    => StockSubjectModes::MODE_JUST_IN_TIME,
                'state'   => StockSubjectStates::STATE_PRE_ORDER,
                'virtual' => 10,
            ],
            $subject,
        ];

        $subject = Fixture::subject(['mode' => StockSubjectModes::MODE_DISABLED]);
        $subject->setStockCompound(true);
        $subject->setStockComposition([
            new StockComponent(
                Fixture::subject([
                    'mode'    => StockSubjectModes::MODE_JUST_IN_TIME,
                    'state'   => StockSubjectStates::STATE_IN_STOCK,
                    'virtual' => 30,
                    'eda'     => $eda = new DateTime('+3 days'),
                ]),
                new Decimal(1)
            ),
            new StockComponent(
                Fixture::subject([
                    'mode'    => StockSubjectModes::MODE_JUST_IN_TIME,
                    'state'   => StockSubjectStates::STATE_IN_STOCK,
                    'virtual' => 30,
                    'eda'     => new DateTime('+2 days'),
                ]),
                new Decimal(2)
            ),
        ]);

        yield 'Compound 5' => [
            [
                'mode'    => StockSubjectModes::MODE_JUST_IN_TIME,
                'state'   => StockSubjectStates::STATE_IN_STOCK,
                'virtual' => 15,
                'eda'     => $eda,
            ],
            $subject,
        ];

        $subject = Fixture::subject(['mode' => StockSubjectModes::MODE_DISABLED]);
        $subject->setStockCompound(true);
        $subject->setStockComposition([
            new StockComponent(
                Fixture::subject([
                    'mode'      => StockSubjectModes::MODE_JUST_IN_TIME,
                    'state'     => StockSubjectStates::STATE_IN_STOCK,
                    'available' => 10,
                    'virtual'   => 30,
                    'eda'       => new DateTime('+3 days'),
                ]),
                new Decimal(1)
            ),
            new StockComponent(
                Fixture::subject([
                    'mode'      => StockSubjectModes::MODE_JUST_IN_TIME,
                    'state'     => StockSubjectStates::STATE_IN_STOCK,
                    'available' => 20,
                    'virtual'   => 30,
                    'eda'       => $eda = new DateTime('+2 days'),
                ]),
                new Decimal(2)
            ),
        ]);

        yield 'Compound 6' => [
            [
                'mode'      => StockSubjectModes::MODE_JUST_IN_TIME,
                'state'     => StockSubjectStates::STATE_IN_STOCK,
                'available' => 10,
                'virtual'   => 15,
                'eda'       => $eda,
            ],
            $subject,
        ];

        $subject = Fixture::subject(['mode' => StockSubjectModes::MODE_DISABLED]);
        $subject->setStockCompound(true);
        $subject->setStockComposition([
            new StockComponent(
                Fixture::subject([
                    'mode'      => StockSubjectModes::MODE_JUST_IN_TIME,
                    'state'     => StockSubjectStates::STATE_IN_STOCK,
                    'in'        => 10,
                    'available' => 10,
                    'virtual'   => 10,
                ]),
                new Decimal(1)
            ),
            new StockComponent(
                Fixture::subject([
                    'mode'    => StockSubjectModes::MODE_JUST_IN_TIME,
                    'state'   => StockSubjectStates::STATE_IN_STOCK,
                    'virtual' => 10,
                    'eda'     => $eda = new DateTime('+2 days'),
                ]),
                new Decimal(1)
            ),
            new StockComponent(
                Fixture::subject([
                    'mode'      => StockSubjectModes::MODE_JUST_IN_TIME,
                    'state'     => StockSubjectStates::STATE_IN_STOCK,
                    'in'        => 20,
                    'available' => 20,
                    'virtual'   => 10,
                ]),
                new Decimal(1)
            ),
        ]);

        yield 'Compound 7' => [
            [
                'mode'    => StockSubjectModes::MODE_JUST_IN_TIME,
                'state'   => StockSubjectStates::STATE_IN_STOCK,
                'virtual' => 10,
                'eda'     => $eda,
            ],
            $subject,
        ];

        $subject = Fixture::subject(['mode' => StockSubjectModes::MODE_DISABLED]);
        $subject->setStockCompound(true);
        $subject->setStockComposition([
            new StockComponent(
                Fixture::subject([
                    'mode'      => StockSubjectModes::MODE_MANUAL,
                    'state'     => StockSubjectStates::STATE_IN_STOCK,
                    'in'        => 20,
                    'available' => 20,
                ]),
                new Decimal(1)
            ),
            new StockComponent(
                Fixture::subject([
                    'mode'      => StockSubjectModes::MODE_MANUAL,
                    'state'     => StockSubjectStates::STATE_IN_STOCK,
                    'in'        => 50,
                    'available' => 50,
                ]),
                new Decimal(3)
            ),
        ]);

        yield 'Compound 8' => [
            [
                'mode'      => StockSubjectModes::MODE_AUTO,
                'state'     => StockSubjectStates::STATE_IN_STOCK,
                'in'        => 16,
                'available' => 16,
                'eda'       => null,
            ],
            $subject,
        ];

        $subject = Fixture::subject(['mode' => StockSubjectModes::MODE_DISABLED]);
        $subject->setStockCompound(true);
        $subject->setStockComposition([
            new StockComponent(
                Fixture::subject([
                    'mode'      => StockSubjectModes::MODE_JUST_IN_TIME,
                    'state'     => StockSubjectStates::STATE_IN_STOCK,
                    'in'        => 20,
                    'available' => 0,
                    'virtual'   => 20,
                    'eda'       => $eda = new DateTime('+2 days'),
                ]),
                new Decimal(1)
            ),
            new StockComponent(
                Fixture::subject([
                    'mode'      => StockSubjectModes::MODE_JUST_IN_TIME,
                    'state'     => StockSubjectStates::STATE_IN_STOCK,
                    'in'        => 20,
                    'available' => 20,
                    'virtual'   => 20,
                    'eda'       => new DateTime('+3 days'),
                ]),
                new Decimal(1)
            ),
        ]);

        yield 'Compound 9' => [
            [
                'mode'      => StockSubjectModes::MODE_JUST_IN_TIME,
                'state'     => StockSubjectStates::STATE_IN_STOCK,
                'in'        => 20,
                'available' => 0,
                'virtual'   => 20,
                'eda'       => $eda,
            ],
            $subject,
        ];

        $subject = Fixture::subject(['mode' => StockSubjectModes::MODE_DISABLED]);
        $subject->setStockCompound(true);
        $subject->setStockComposition([
            [
                new StockComponent(
                    Fixture::subject([
                        'mode'  => StockSubjectModes::MODE_DISABLED,
                        'state' => StockSubjectStates::STATE_IN_STOCK,
                    ]),
                    new Decimal(1)
                ),
                new StockComponent(
                    Fixture::subject([
                        'mode'  => StockSubjectModes::MODE_AUTO,
                        'state' => StockSubjectStates::STATE_IN_STOCK,
                        'in'    => 40,
                    ]),
                    new Decimal(3)
                ),
            ],
            [
                new StockComponent(
                    Fixture::subject([
                        'mode'    => StockSubjectModes::MODE_JUST_IN_TIME,
                        'state'   => StockSubjectStates::STATE_IN_STOCK,
                        'virtual' => 10,
                        'eda'     => $eda = new DateTime('+2 days'),
                    ]),
                    new Decimal(1)
                ),
                new StockComponent(
                    Fixture::subject([
                        'mode'    => StockSubjectModes::MODE_JUST_IN_TIME,
                        'state'   => StockSubjectStates::STATE_IN_STOCK,
                        'virtual' => 20,
                        'eda'     => new DateTime('+3 days'),
                    ]),
                    new Decimal(2)
                ),
            ],
        ]);

        yield 'Compound 10' => [
            [
                'mode'    => StockSubjectModes::MODE_JUST_IN_TIME,
                'state'   => StockSubjectStates::STATE_IN_STOCK,
                'virtual' => 10,
                'eda'     => $eda,
            ],
            $subject,
        ];
    }
}
