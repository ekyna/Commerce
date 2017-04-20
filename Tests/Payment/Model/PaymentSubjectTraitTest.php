<?php /** @noinspection PhpMethodNamingConventionInspection */

namespace Ekyna\Component\Commerce\Tests\Payment\Model;

use Decimal\Decimal;
use Ekyna\Component\Commerce\Common\Model\CurrencyInterface;
use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Order\Entity\Order;
use Ekyna\Component\Commerce\Tests\Fixture;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Class PaymentSubjectTraitTest
 * @package Ekyna\Component\Commerce\Tests\Payment\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class PaymentSubjectTraitTest extends TestCase
{
    /**
     * @param SaleInterface $sale
     * @param bool         $expected
     *
     * @dataProvider provide_isPaid
     */
    public function test_isPaid(SaleInterface $sale, bool $expected): void
    {
        self::assertEquals($expected, $sale->isPaid());
    }

    public function provide_isPaid(): array
    {
        return [
            // (Grand, Deposit, Pending, Outstanding, Paid), Paid
            [$this->createSubject(100, 0,   0,   0,    0), false], // 0
            [$this->createSubject(100, 0,  40,   0,    0), false], // 1
            [$this->createSubject(100, 0, 100,   0,    0), false], // 2
            [$this->createSubject(100, 0,   0,  40,    0), false], // 3
            [$this->createSubject(100, 0,   0, 100,    0), false], // 4
            [$this->createSubject(100, 0,   0,   0,   40), false], // 5
            [$this->createSubject(100, 0,   0,   0,  100),  true], // 6

            [$this->createSubject(100, 0,  30,  30,    0), false], // 7
            [$this->createSubject(100, 0,  30,   0,   30), false], // 8
            [$this->createSubject(100, 0,   0,  30,   30), false], // 9

            [$this->createSubject(100, 40,  0,   0,    0), false], // 10
            [$this->createSubject(100, 40, 40,   0,    0), false], // 11
            [$this->createSubject(100, 40, 40,  60,    0), false], // 12
            [$this->createSubject(100, 40,  0,   0,   40), false], // 13
            [$this->createSubject(100, 40,  0,   0,   60), false], // 14
            [$this->createSubject(100, 40,  0,   0,  100),  true], // 15
        ];
    }

    private function createSubject(
        float $grand,
        float $deposit,
        float $pending,
        float $outstanding,
        float $paid
    ): SaleInterface {
        /** @var CurrencyInterface|MockObject $currency */
        $currency = $this->createMock(CurrencyInterface::class);
        $currency->method('getCode')->willReturn(Fixture::CURRENCY_EUR);

        return (new Order())
            ->setCurrency($currency)
            ->setGrandTotal(new Decimal($grand))
            ->setDepositTotal(new Decimal($deposit))
            ->setPendingTotal(new Decimal($pending))
            ->setOutstandingAccepted(new Decimal($outstanding))
            ->setPaidTotal(new Decimal($paid));
    }
}
