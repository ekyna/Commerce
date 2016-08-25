<?php

namespace Ekyna\Component\Commerce\Tests\Common\Calculator;

use Ekyna\Component\Commerce\Common\Calculator\Result;
use Ekyna\Component\Commerce\Common\Calculator\Calculator;
use Ekyna\Component\Commerce\Common\Calculator\CalculatorInterface;
use Ekyna\Component\Commerce\Tests\OrmTestCase;

/**
 * Class CalculatorTest
 * @package Ekyna\Component\Commerce\Tests\Common\Calculator
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CalculatorTest extends OrmTestCase
{
    const MODES = [
        CalculatorInterface::MODE_NET   => 0,
        CalculatorInterface::MODE_GROSS => 1,
    ];

    /**
     * @var Calculator
     */
    private static $calculator;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();

        static::$calculator = new Calculator();
    }

    public static function tearDownAfterClass()
    {
        parent::tearDownAfterClass();

        static::$calculator = null;
    }

    public function testCalculateSaleItem()
    {
        $cases = [
            'Case 1'  => [
                'order_item_id' => 1,
                'base'  => [124.17, 124.17],
                'total' => [149.00, 149.00],
                'taxes' => [
                    'TVA 20%' => [24.83, 24.83],
                ],
            ],
            'Case 2'  => [
                'order_item_id' => 2,
                'base'  => [231.66, 231.67],
                'total' => [277.99, 278.00],
                'taxes' => [
                    'TVA 20%' => [46.33, 46.33],
                ],
            ],
            'Case 3'  => [
                'order_item_id' => 3,
                'base'  => [374.97, 374.98],
                'total' => [449.96, 449.97],
                'taxes' => [
                    'TVA 20%' => [74.99, 74.99],
                ],
            ],
            'Case 4'  => [
                'order_item_id' => 4,
                'base'  => [229.04, 229.03],
                'total' => [251.94, 251.93],
                'taxes' => [
                    'TVA 10%' => [22.90, 22.90],
                ],
            ],
            'Case 5'  => [
                'order_item_id' => 5,
                'base'  => [303.95, 303.96],
                'total' => [364.74, 364.75],
                'taxes' => [
                    'TVA 20%' => [60.79, 60.79],
                ],
            ],
            'Case 6'  => [
                'order_item_id' => 6,
                'base'  => [2765.98, 2766.06],
                'total' => [3042.58, 3042.67],
                'taxes' => [
                    'TVA 10%' => [276.60, 276.61],
                ],
            ],
            'Case 7'  => [
                'order_item_id' => 7,
                'base'  => [2862.30, 2862.43],
                'total' => [3249.40, 3249.54],
                'taxes' => [
                    'TVA 20%' => [201.74, 201.74],
                    'TVA 10%' => [185.36, 185.37],
                ],
            ],
            'Case 8'  => [
                'order_item_id' => 8,
                'base'  => [1008.70, 1008.70],
                'total' => [1210.44, 1210.44],
                'taxes' => [
                    'TVA 20%' => [201.74, 201.74],
                ],
            ],
            'Case 9'  => [
                'order_item_id' => 9,
                'base'  => [1853.60, 1853.73],
                'total' => [2038.96, 2039.10],
                'taxes' => [
                    'TVA 10%' => [185.36, 185.37],
                ],
            ],
            'Case 10' => [
                'order_item_id' => 10,
                'base'  => [1338.17, 1338.11],
                'total' => [1559.45, 1559.37],
                'taxes' => [
                    'TVA 20%' => [174.92, 174.91],
                    'TVA 10%' => [ 46.36,  46.35],
                ],
            ],
            'Case 11' => [
                'order_item_id' => 11,
                'base'  => [ 920.65,  920.63],
                'total' => [1104.78, 1104.75],
                'taxes' => [
                    'TVA 20%' => [184.13, 184.12],
                ],
            ],
            'Case 12' => [
                'order_item_id' => 12,
                'total' => [536.75, 536.70],
                'base'  => [487.95, 487.91],
                'taxes' => [
                    'TVA 10%' => [48.80, 48.79],
                ],
            ],
            /*'Case 13' => [
                'order_item_id' => 13,
                'total' => [79153.84, 79154.25],
                'base'  => [70054.37, 70054.71],
                'taxes' => [
                    'TVA 20%' => [8548.22, 8548.29],
                    'TVA 10%' => [ 551.25,  551.25],
                ],
            ],*/
            'Case 14' => [
                'order_item_id' => 14,
                'total' => [51289.33, 51289.74],
                'base'  => [42741.11, 42741.45],
                'taxes' => [
                    'TVA 20%' => [8548.22, 8548.29],
                ],
            ],
            'Case 15' => [
                'order_item_id' => 15,
                'base'  => [5512.47, 5512.47],
                'total' => [6063.72, 6063.72],
                'taxes' => [
                    'TVA 10%' => [551.25, 551.25],
                ],
            ],
            'Case 16' => [
                'order_item_id' => 16,
                'total' => [21800.79, 21800.79],
                'base'  => [21800.79, 21800.79],
                'taxes' => [], // No tax
            ],
        ];

        foreach (self::MODES as $mode => $offset) {
            static::$calculator->setMode($mode);

            foreach ($cases as $name => $data) {
                /** @var \Ekyna\Component\Commerce\Order\Model\OrderItemInterface $item */
                $item = $this->find('orderItem', $data['order_item_id']);

                $amounts = static::$calculator->calculateSaleItem($item);

                $this->assertAmounts($amounts, $data, $name, $mode, $offset);
            }
        }
    }

    public function testCalculateSale()
    {
        $cases = [
            'Case 1' => [
                'order_id' => 1,
                'base'     => [355.83, 355.84],
                'total'    => [426.99, 427.00],
                'taxes'    => [
                    'TVA 20%' => [71.16, 71.16],
                ]
            ],
            'Case 2' => [
                'order_id' => 2,
                'base'     => [554.01, 554.01],
                'total'    => [651.90, 651.90],
                'taxes'    => [
                    'TVA 20%' => [74.99, 74.99],
                    'TVA 10%' => [22.90, 22.90],
                ]
            ],
            'Case 3' => [
                'order_id' => 3,
                'base'     => [2916.43, 2916.52],
                'total'    => [3236.95, 3237.05],
                'taxes'    => [
                    'TVA 20%' => [ 57.75,  57.75],
                    'TVA 10%' => [262.77, 262.78],
                ]
            ],
            'Case 4' => [
                'order_id' => 4,
                'base'     => [2862.30, 2862.43],
                'total'    => [3249.40, 3249.54],
                'taxes'    => [
                    'TVA 20%' => [201.74, 201.74],
                    'TVA 10%' => [185.36, 185.37],
                ]
            ],
            'Case 5' => [
                'order_id' => 5,
                'base'     => [1318.17, 1318.11],
                'total'    => [1539.45, 1539.37],
                'taxes'    => [
                    'TVA 20%' => [174.92, 174.91],
                    'TVA 10%' => [ 46.36,  46.35],
                ]
            ],
            'Case 6' => [
                'order_id' => 6,
                'base'     => [70054.37, 70054.71],
                'total'    => [79153.84, 79154.25],
                'taxes'    => [
                    'TVA 20%' => [8548.22, 8548.29],
                    'TVA 10%' => [ 551.25,  551.25],
                ]
            ],
        ];

        foreach (self::MODES as $mode => $offset) {
            static::$calculator->setMode($mode);

            foreach ($cases as $name => $data) {
                /** @var \Ekyna\Component\Commerce\Order\Model\OrderInterface $order */
                $order = $this->find('order', $data['order_id']);

                $amounts = static::$calculator->calculateSale($order);

                $this->assertAmounts($amounts, $data, $name, $mode, $offset);
            }
        }
    }

    /**
     * Asserts that the amounts are correct.
     *
     * @param Result $amounts
     * @param array  $data
     * @param string $name
     * @param int    $mode
     * @param int    $offset
     */
    private function assertAmounts(Result $amounts, array $data, $name, $mode, $offset)
    {
        // Asserts that the BASE is correctly calculated.
        $this->assertEquals(
            $data['base'][$offset],
            $amounts->getBase(),
            "Test '$name' ['$mode' mode] : wrong base."
        );

        // Asserts that the TOTAL is correctly calculated.
        $this->assertEquals(
            $data['total'][$offset],
            $amounts->getTotal(),
            "Test '$name' ['$mode' mode] : wrong total."
        );

        // Asserts that the TAXES count is correct.
        $this->assertEquals(
            count($data['taxes']),
            count($amounts->getTaxes()),
            "Test '$name' ['$mode' mode] : wrong taxes count."
        );

        foreach ($data['taxes'] as $taxName => $taxTotal) {
            $found = false;
            foreach ($amounts->getTaxes() as $taxAmount) {
                if ($taxAmount->getName() === $taxName) {
                    $found = true;

                    // Asserts that the TAX amount is correctly calculated.
                    $this->assertEquals(
                        $taxTotal[$offset],
                        $taxAmount->getAmount(),
                        "Test '$name' base ['$mode' mode] : wrong tax '$taxName' total."
                    );

                    break;
                }
            }

            // Asserts that the TAX has been found.
            $this->assertEquals(
                true,
                $found,
                "Test '$name' ['$mode' mode] : tax '$taxName' not found."
            );
        }
    }

    public function testCalculateDiscountAdjustment()
    {
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }
}
