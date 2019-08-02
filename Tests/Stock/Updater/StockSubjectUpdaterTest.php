<?php

namespace Ekyna\Component\Commerce\Tests\Stock\Updater;

use Acme\Product\Entity\Product;
use Acme\Product\Entity\StockUnit;
use Ekyna\Component\Commerce\Stock\Model\StockComponent;
use Ekyna\Component\Commerce\Stock\Model\StockSubjectModes;
use Ekyna\Component\Commerce\Stock\Model\StockSubjectStates;
use Ekyna\Component\Commerce\Stock\Model\StockUnitStates;
use Ekyna\Component\Commerce\Stock\Resolver\StockUnitResolverInterface;
use Ekyna\Component\Commerce\Stock\Updater\StockSubjectUpdater;
use Ekyna\Component\Commerce\Supplier\Repository\SupplierProductRepositoryInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Class StockSubjectUpdaterTest
 * @package Ekyna\Component\Commerce\Tests\Stock\Updater
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class StockSubjectUpdaterTest extends TestCase
{
    /**
     * @var StockUnitResolverInterface|MockObject
     */
    private $stockUnitResolver;

    /**
     * @var SupplierProductRepositoryInterface|MockObject
     */
    private $supplierProductRepository;

    /**
     * @var StockSubjectUpdater
     */
    private $updater;


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
     * @param array          $result
     * @param Product        $subject
     * @param array          $units
     * @param \DateTime|null $eda
     * @param float          $available
     *
     * @dataProvider provide_update
     */
    public function test_update(
        array $result,
        Product $subject,
        array $units = [],
        \DateTime $eda = null,
        float $available = .0
    ) {
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
            ->willReturn($available);

        $this->updater->update($subject);

        $this->assertEquals($result['mode'], $subject->getStockMode());
        $this->assertEquals($result['state'], $subject->getStockState());
        $this->assertEquals($result['in'], $subject->getInStock());
        $this->assertEquals($result['available'], $subject->getAvailableStock());
        $this->assertEquals($result['virtual'], $subject->getVirtualStock());
        $this->assertEquals($result['eda'], $subject->getEstimatedDateOfArrival());
    }

    public function provide_update(): \Generator
    {
        $subject = $this->createSubject(StockSubjectModes::MODE_DISABLED);
        yield 'Simple 1' => [
            [
                'mode'      => StockSubjectModes::MODE_DISABLED,
                'state'     => StockSubjectStates::STATE_IN_STOCK,
                'in'        => 0.0,
                'available' => 0.0,
                'virtual'   => 0.0,
                'eda'       => null,
            ],
            $subject,
        ];

        $subject = $this->createSubject(StockSubjectModes::MODE_AUTO);
        yield 'Simple 2' => [
            [
                'mode'      => StockSubjectModes::MODE_AUTO,
                'state'     => StockSubjectStates::STATE_OUT_OF_STOCK,
                'in'        => 0.0,
                'available' => 0.0,
                'virtual'   => -10.0,
                'eda'       => null,
            ],
            $subject,
            [
                $this->createStockUnit(StockUnitStates::STATE_NEW, 10),
            ],
        ];

        $subject = $this->createSubject(StockSubjectModes::MODE_AUTO);
        $eda = new \DateTime('+1 day');
        yield 'Simple 3' => [
            [
                'mode'      => StockSubjectModes::MODE_AUTO,
                'state'     => StockSubjectStates::STATE_OUT_OF_STOCK,
                'in'        => 5.0,
                'available' => 0.0,
                'virtual'   => 0.0,
                'eda'       => $eda,
            ],
            $subject,
            [
                $this->createStockUnit(StockUnitStates::STATE_READY, 20, 10, 0, 20, 15, $eda),
            ],
        ];

        $subject = $this->createSubject(StockSubjectModes::MODE_AUTO);
        yield 'Simple 4' => [
            [
                'mode'      => StockSubjectModes::MODE_AUTO,
                'state'     => StockSubjectStates::STATE_IN_STOCK,
                'in'        => 10.0,
                'available' => 10.0,
                'virtual'   => 10.0,
                'eda'       => null,
            ],
            $subject,
            [
                $this->createStockUnit(StockUnitStates::STATE_READY, 10, 10, 0, 20, 20),
            ],
        ];

        $subject = $this->createSubject(StockSubjectModes::MODE_AUTO);
        $eda = new \DateTime('+1 day');
        yield 'Simple 5' => [
            [
                'mode'      => StockSubjectModes::MODE_AUTO,
                'state'     => StockSubjectStates::STATE_PRE_ORDER,
                'in'        => 5.0,
                'available' => 0.0,
                'virtual'   => 5.0,
                'eda'       => $eda,
            ],
            $subject,
            [
                $this->createStockUnit(StockUnitStates::STATE_READY, 15, 10, 0, 20, 15, $eda),
                $this->createStockUnit(StockUnitStates::STATE_PENDING, 20, 0, 0, 20, 0, new \DateTime('+2 day')),
            ],
        ];

        // EDA from supplier data
        $subject = $this->createSubject(StockSubjectModes::MODE_AUTO);
        $eda = new \DateTime('+2 day');
        yield 'Simple 6' => [
            [
                'mode'      => StockSubjectModes::MODE_AUTO,
                'state'     => StockSubjectStates::STATE_PRE_ORDER,
                'in'        => 0.0,
                'available' => 0.0,
                'virtual'   => 0.0,
                'eda'       => $eda,
            ],
            $subject,
            [],
            $eda,
            10,
        ];

        $subject =$this->createSubject(StockSubjectModes::MODE_JUST_IN_TIME);
        yield 'Simple 7' => [
            [
                'mode'      => StockSubjectModes::MODE_JUST_IN_TIME,
                'state'     => StockSubjectStates::STATE_PRE_ORDER,
                'in'        => 0.0,
                'available' => 0.0,
                'virtual'   => 0.0,
                'eda'       => null,
            ],
            $subject,
            [],
        ];

        $subject = $this->createSubject(StockSubjectModes::MODE_JUST_IN_TIME);
        $eda = new \DateTime('+1 day');
        yield 'Simple 8' => [
            [
                'mode'      => StockSubjectModes::MODE_JUST_IN_TIME,
                'state'     => StockSubjectStates::STATE_IN_STOCK,
                'in'        => 0.0,
                'available' => 0.0,
                'virtual'   => 20.0,
                'eda'       => $eda,
            ],
            $subject,
            [
                $this->createStockUnit(StockUnitStates::STATE_PENDING, 0, 0, 0, 20, 0, $eda),
            ],
        ];

        // ---------------------------------------- COMPOUND ----------------------------------------

        $subject = $this->createSubject();
        $subject->setStockCompound(true);
        $subject->setStockComposition([
            new StockComponent(
                $this->createSubject(),
                1
            ),
        ]);

        yield 'Compound 1' => [
            [
                'mode'      => StockSubjectModes::MODE_DISABLED,
                'state'     => StockSubjectStates::STATE_IN_STOCK,
                'in'        => 0.0,
                'available' => 0.0,
                'virtual'   => 0.0,
                'eda'       => null,
            ],
            $subject,
        ];

        $subject = $this->createSubject();
        $subject->setStockCompound(true);
        $subject->setStockComposition([
            new StockComponent(
                $this->createSubject(
                    StockSubjectModes::MODE_AUTO,
                    StockSubjectStates::STATE_IN_STOCK,
                    20, 20, 0, null
                ),
                1
            ),
            new StockComponent(
                $this->createSubject(
                    StockSubjectModes::MODE_AUTO,
                    StockSubjectStates::STATE_IN_STOCK,
                    20, 20, 0
                ),
                2
            ),
        ]);

        yield 'Compound 2' => [
            [
                'mode'      => StockSubjectModes::MODE_AUTO,
                'state'     => StockSubjectStates::STATE_IN_STOCK,
                'in'        => 10.0,
                'available' => 10.0,
                'virtual'   => 0.0,
                'eda'       => null,
            ],
            $subject,
        ];

        $subject = $this->createSubject();
        $subject->setStockCompound(true);
        $subject->setStockComposition([
            new StockComponent(
                $this->createSubject(
                    StockSubjectModes::MODE_DISABLED,
                    StockSubjectStates::STATE_IN_STOCK,
                    0, 0, 0, null
                ),
                1
            ),
            new StockComponent(
                $this->createSubject(
                    StockSubjectModes::MODE_AUTO,
                    StockSubjectStates::STATE_IN_STOCK,
                    30, 30, 0, null
                ),
                3
            ),
        ]);

        yield 'Compound 3' => [
            [
                'mode'      => StockSubjectModes::MODE_AUTO,
                'state'     => StockSubjectStates::STATE_IN_STOCK,
                'in'        => 10.0,
                'available' => 10.0,
                'virtual'   => 0.0,
                'eda'       => null,
            ],
            $subject,
        ];

        $subject = $this->createSubject();
        $subject->setStockCompound(true);
        $subject->setStockComposition([
            new StockComponent(
                $this->createSubject(
                    StockSubjectModes::MODE_JUST_IN_TIME,
                    StockSubjectStates::STATE_PRE_ORDER,
                    0, 0, 0, null
                ),
                1
            ),
            new StockComponent(
                $this->createSubject(
                    StockSubjectModes::MODE_JUST_IN_TIME,
                    StockSubjectStates::STATE_IN_STOCK,
                    0, 0, 30, new \DateTime('+3 days')
                ),
                3
            ),
        ]);

        yield 'Compound 4' => [
            [
                'mode'      => StockSubjectModes::MODE_JUST_IN_TIME,
                'state'     => StockSubjectStates::STATE_PRE_ORDER,
                'in'        => 0.0,
                'available' => 0.0,
                'virtual'   => 0.0,
                'eda'       => null,
            ],
            $subject,
        ];

        $subject = $this->createSubject();
        $subject->setStockCompound(true);
        $subject->setStockComposition([
            new StockComponent(
                $this->createSubject(
                    StockSubjectModes::MODE_JUST_IN_TIME,
                    StockSubjectStates::STATE_IN_STOCK,
                    0, 0, 30, new \DateTime('+2 days')
                ),
                1
            ),
            new StockComponent(
                $this->createSubject(
                    StockSubjectModes::MODE_JUST_IN_TIME,
                    StockSubjectStates::STATE_IN_STOCK,
                    0, 0, 30, $eda = new \DateTime('+3 days')
                ),
                2
            ),
        ]);

        yield 'Compound 5' => [
            [
                'mode'      => StockSubjectModes::MODE_JUST_IN_TIME,
                'state'     => StockSubjectStates::STATE_IN_STOCK,
                'in'        => 0.0,
                'available' => 0.0,
                'virtual'   => 15.0,
                'eda'       => $eda,
            ],
            $subject,
        ];

        $subject = $this->createSubject();
        $subject->setStockCompound(true);
        $subject->setStockComposition([
            new StockComponent(
                $this->createSubject(
                    StockSubjectModes::MODE_MANUAL,
                    StockSubjectStates::STATE_IN_STOCK,
                    20, 20, 0, null
                ),
                1
            ),
            new StockComponent(
                $this->createSubject(
                    StockSubjectModes::MODE_MANUAL,
                    StockSubjectStates::STATE_IN_STOCK,
                    50, 50, 0, null
                ),
                3
            ),
        ]);

        yield 'Compound 6' => [
            [
                'mode'      => StockSubjectModes::MODE_AUTO,
                'state'     => StockSubjectStates::STATE_IN_STOCK,
                'in'        => 16.0,
                'available' => 16.0,
                'virtual'   => 0.0,
                'eda'       => null,
            ],
            $subject,
        ];

        $subject = $this->createSubject();
        $subject->setStockCompound(true);
        $subject->setStockComposition([
            [
                new StockComponent(
                    $this->createSubject(
                        StockSubjectModes::MODE_DISABLED,
                        StockSubjectStates::STATE_IN_STOCK,
                        0, 0, 0, null
                    ),
                    1
                ),
                new StockComponent(
                    $this->createSubject(
                        StockSubjectModes::MODE_AUTO,
                        StockSubjectStates::STATE_IN_STOCK,
                        60, 60, 0, null
                    ),
                    3
                ),
            ],
            [
                new StockComponent(
                    $this->createSubject(
                        StockSubjectModes::MODE_JUST_IN_TIME,
                        StockSubjectStates::STATE_IN_STOCK,
                        0, 0, 30, new \DateTime('+2 days')
                    ),
                    1
                ),
                new StockComponent(
                    $this->createSubject(
                        StockSubjectModes::MODE_JUST_IN_TIME,
                        StockSubjectStates::STATE_IN_STOCK,
                        0, 0, 30, $eda = new \DateTime('+3 days')
                    ),
                    2
                ),
            ],
        ]);

        yield 'Compound 7' => [
            [
                'mode'      => StockSubjectModes::MODE_AUTO,
                'state'     => StockSubjectStates::STATE_PRE_ORDER,
                'in'        => 0.0,
                'available' => 0.0,
                'virtual'   => 15.0,
                'eda'       => $eda,
            ],
            $subject,
        ];
    }

    private function createStockUnit(
        string $state = StockUnitStates::STATE_NEW,
        float $sold = .0,
        float $shipped = .0,
        float $adjusted = .0,
        float $ordered = .0,
        float $received = .0,
        \DateTime $eda = null
    ): StockUnit {
        $unit = new StockUnit();

        $unit
            ->setState($state)
            ->setSoldQuantity($sold)
            ->setShippedQuantity($shipped)
            ->setAdjustedQuantity($adjusted)
            ->setOrderedQuantity($ordered)
            ->setReceivedQuantity($received)
            ->setEstimatedDateOfArrival($eda);

        return $unit;
    }

    private function createSubject(
        string $mode = StockSubjectModes::MODE_DISABLED,
        string $state = StockSubjectStates::STATE_IN_STOCK,
        float $in = 0.0,
        float $available = 0.0,
        float $virtual = 0.0,
        \DateTime $eda = null
    ): Product {
        $subject = new Product();
        $subject
            ->setStockMode($mode)
            ->setStockState($state)
            ->setInStock($in)
            ->setAvailableStock($available)
            ->setVirtualStock($virtual)
            ->setEstimatedDateOfArrival($eda);

        return $subject;
    }
}
