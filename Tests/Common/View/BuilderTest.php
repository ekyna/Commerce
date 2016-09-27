<?php

namespace Ekyna\Component\Commerce\Tests\Common\View;

use Ekyna\Component\Commerce\Common\Calculator\AmountsCalculator;
use Ekyna\Component\Commerce\Common\View\ViewBuilder;
use Ekyna\Component\Commerce\Tests\OrmTestCase;

/**
 * Class BuilderTest
 * @package Ekyna\Component\Commerce\Tests\Common\View
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class BuilderTest extends OrmTestCase
{
    /**
     * @var ViewBuilder
     */
    private static $builder;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();

        static::$builder = new ViewBuilder(
            new AmountsCalculator()
        );
    }

    public static function tearDownAfterClass()
    {
        parent::tearDownAfterClass();

        static::$builder = null;
    }

    public function testBuildSaleView()
    {
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }
}
