<?php

namespace Ekyna\Component\Commerce\Tests\Payment\Updater;

use Ekyna\Component\Commerce\Common\Currency\ArrayCurrencyConverter;
use Ekyna\Component\Commerce\Common\Currency\CurrencyConverterInterface;
use Ekyna\Component\Commerce\Payment\Updater\PaymentUpdater;
use PHPUnit\Framework\TestCase;

/**
 * Class PaymentUpdaterTest
 * @package Ekyna\Component\Commerce\Tests\Payment\Updater
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class PaymentUpdaterTest extends TestCase
{
    /**
     * @var CurrencyConverterInterface
     */
    private $converter;

    /**
     * @var PaymentUpdater
     */
    private $updater;


    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->converter = new ArrayCurrencyConverter([
            'EUR/USD' => 1.25,
            'USD/EUR' => 0.80,
        ], 'EUR');

        $this->updater = new PaymentUpdater($this->converter);
    }

    protected function tearDown(): void
    {
        $this->updater = null;
        $this->converter = null;
    }

    public function test_updateExchangeRate(): void
    {
        $this->markTestIncomplete(); // TODO
    }

    public function test_updateAmount(): void
    {
        $this->markTestIncomplete(); // TODO
    }

    public function test_updateRealAmount(): void
    {
        $this->markTestIncomplete(); // TODO
    }

    public function test_fixAmount(): void
    {
        $this->markTestIncomplete(); // TODO
    }

    public function test_fixRealAmount(): void
    {
        $this->markTestIncomplete(); // TODO
    }
}