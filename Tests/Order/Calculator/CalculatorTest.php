<?php

namespace Ekyna\Component\Commerce\Tests\Order\Calculator;

use Ekyna\Component\Commerce\Order\Calculator\Calculator;
use Ekyna\Component\Commerce\Order\Calculator\CalculatorInterface;
use Ekyna\Component\Commerce\Tests\OrmTestCase;

/**
 * Class CalculatorTest
 * @package Ekyna\Component\Commerce\Tests\Order\Calculator
 * @author  Etienne Dauvergne <contact@ekyna.com>
 *
 * @coversDefaultClass \Ekyna\Component\Commerce\Order\Calculator\Calculator
 */
class CalculatorTest extends OrmTestCase
{
    /**
     * @var Calculator
     */
    private $calculator;

    public function setUp()
    {
        parent::setUp();

        $this->calculator = new Calculator();
    }

    public function tearDown()
    {
        parent::tearDown();

        $this->calculator = null;
    }

    /**
     * @covers ::buildOrderItemTotal
     */
    public function testBuildOrderItemTotal()
    {
        $cases = [
            'Case 1'  => ['order_item_id' => 1,  'total' => [ 149.00,   149.00]],
            'Case 2'  => ['order_item_id' => 2,  'total' => [ 277.99,   278.00]],
            'Case 3'  => ['order_item_id' => 3,  'total' => [ 449.96,   449.97]],
            'Case 4'  => ['order_item_id' => 4,  'total' => [ 251.94,   251.93]],
            'Case 5'  => ['order_item_id' => 5,  'total' => [ 364.74,   364.75]],
            'Case 6'  => ['order_item_id' => 6,  'total' => [3042.58,  3042.67]],
            'Case 7'  => ['order_item_id' => 7,  'total' => [3249.40,  3249.54]],
            'Case 8'  => ['order_item_id' => 8,  'total' => [ 172.92,   172.92]],
            'Case 9'  => ['order_item_id' => 9,  'total' => [ 291.28,   291.30]],
            'Case 10' => ['order_item_id' => 10, 'total' => [1571.00,  1571.00]],
//            'Case 11' => ['order_item_id' => 11, 'total' => [220.94, 220.94]],
//            'Case 12' => ['order_item_id' => 12, 'total' => [107.35, 107.35]],
//            'Case 13' => ['order_item_id' => 13, 'total' => [73663.38, 73663.38]],
//            'Case 14' => ['order_item_id' => 14, 'total' => [979.79, 979.79]],
//            'Case 15' => ['order_item_id' => 15, 'total' => [106.89, 106.89]],
//            'Case 16' => ['order_item_id' => 16, 'total' => [382.47, 382.47]],
        ];

        $modes = [
            CalculatorInterface::MODE_NET => 0,
            CalculatorInterface::MODE_GROSS => 1,
        ];

        foreach ($modes as $mode => $offset) {
            $this->calculator->setMode($mode);

            foreach ($cases as $name => $case) {
                /** @var \Ekyna\Component\Commerce\Order\Model\OrderItemInterface $item */
                $item = $this->find('orderItem', $case['order_item_id']);

                $total = $this->calculator->buildOrderItemAmounts($item);

                $this->assertEquals(
                    $case['total'][$offset],
                    $this->calculator->calculateGrossTotal($total),
                    "Test '$name' [$mode based] failed."
                );
            }
        }

        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    /**
     * @covers ::buildOrderTotal
     */
    public function testBuildOrderTotal()
    {
        $cases = [
            ['order_id' => 1, 'total' => 427.00],
            ['order_id' => 2, 'total' => 278.00],
        ];

//        foreach ($cases as $case) {
//            /** @var \Ekyna\Component\Commerce\Order\Model\OrderItemInterface $item */
//            $item = $this->find('orderItem', $case['order_item_id']);
//
//            $total = new Total();
//            $this->calculator->buildOrderItemTotal($item, $total);
//
//            $this->assertEquals($case['total'], $this->calculator->calculateTotal($total));
//        }

        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }
}
