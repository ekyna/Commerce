<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Tests\Invoice\Resolver;

use Decimal\Decimal;
use Ekyna\Component\Commerce\Invoice\Calculator\InvoiceSubjectCalculatorInterface;
use Ekyna\Component\Commerce\Invoice\Model\InvoiceStates;
use Ekyna\Component\Commerce\Invoice\Resolver\InvoiceSubjectStateResolver;
use Ekyna\Component\Commerce\Order\Model\OrderInterface;
use Generator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * Class InvoiceSubjectStateResolverTest
 * @package Ekyna\Component\Commerce\Tests\Invoice\Resolver
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class InvoiceSubjectStateResolverTest extends TestCase
{
    private InvoiceSubjectCalculatorInterface|MockObject|null $calculator;
    private ?InvoiceSubjectStateResolver                      $resolver;

    protected function setUp(): void
    {
        $this->calculator = $this->createMock(InvoiceSubjectCalculatorInterface::class);
        $this->resolver = new InvoiceSubjectStateResolver($this->calculator);
    }

    protected function tearDown(): void
    {
        $this->resolver = null;
        $this->calculator = null;
    }


    /**
     * @dataProvider provideResolveState
     */
    public function testResolveState(string $expected, array $map): void
    {
        $map = array_map(function ($item) {
            return array_map(fn($v) => new Decimal($v), array_replace([
                'total'    => 0,
                'invoiced' => 0,
                'adjusted' => 0,
                'credited' => 0,
                'shipped'  => 0,
                'returned' => 0,
            ], $item));
        }, $map);

        $subject = $this->createMock(OrderInterface::class);

        $this
            ->calculator
            ->expects(self::once())
            ->method('buildInvoiceQuantityMap')
            ->with($subject)
            ->willReturn($map);

        $rc = new ReflectionClass(InvoiceSubjectStateResolver::class);
        $rm = $rc->getMethod('resolveState');

        self::assertEquals($expected, $rm->invoke($this->resolver, $subject));
    }

    public function provideResolveState(): Generator
    {
        yield 'New case 1' => [
            InvoiceStates::STATE_NEW,
            [],
        ];

        yield 'Credited case 1' => [
            InvoiceStates::STATE_CREDITED,
            [
                ['total' => 10, 'invoiced' => 10, 'credited' => 10],
            ],
        ];

        yield 'Completed case 1' => [
            InvoiceStates::STATE_COMPLETED,
            [
                ['total' => 10, 'invoiced' => 10, 'credited' => 0],
            ],
        ];

        yield 'Partial case 1' => [
            InvoiceStates::STATE_PARTIAL,
            [
                ['total' => 10, 'invoiced' => 8, 'credited' => 0],
            ],
        ];

        yield 'New case 2' => [
            InvoiceStates::STATE_NEW,
            [
                ['total' => 10, 'invoiced' => 0, 'credited' => 0],
            ],
        ];
    }
}
