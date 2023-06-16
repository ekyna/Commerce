<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Tests\Common\Calculator;

use Decimal\Decimal;
use Ekyna\Component\Commerce\Common\Calculator\ItemCostCalculator;
use Ekyna\Component\Commerce\Common\Model\Cost;
use Ekyna\Component\Commerce\Common\Model\SaleItemInterface;
use Ekyna\Component\Commerce\Subject\Model\SubjectInterface;
use Ekyna\Component\Commerce\Tests\Data;
use Ekyna\Component\Commerce\Tests\Fixture;
use Ekyna\Component\Commerce\Tests\TestCase;
use Ekyna\Component\Commerce\Tests\Util;
use Generator;

/**
 * Class ItemCostCalculatorTest
 * @package Ekyna\Component\Commerce\Tests\Common\Calculator
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class ItemCostCalculatorTest extends TestCase
{
    private ItemCostCalculator|null $itemCostCalculator;

    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->getSubjectHelperMock()
            ->method('resolve')
            ->willReturnCallback(
                static fn(SaleItemInterface $item): ?SubjectInterface => $item->getSubjectIdentity()->getSubject()
            );

        $this->itemCostCalculator = new ItemCostCalculator(
            $this->getSubjectHelperMock(),
            $this->getPurchaseCostGuesserMock()
        );
    }

    protected function tearDown(): void
    {
        $this->itemCostCalculator = null;

        parent::tearDown();
    }

    /**
     * @dataProvider provideData()
     */
    public function testCalculateSaleItem(array $data, array $subjectCosts): void
    {
        Fixture::order($data);

        $this->configureSubjectCostGuesser($subjectCosts);

        foreach ($this->provideDefaultResults($data['items']) as $reference => $expected) {
            $item = Fixture::get($reference);

            $result = $this->itemCostCalculator->calculateSaleItem($item);

            self::assertEquals($expected, $result, "Unexpected cost for item $reference.");
        }
    }

    public function provideData(): Generator
    {
        yield 'Order 3' => [
            Data::order3(),
            [
                'subject2' => new Cost(new Decimal('52.31')),
            ],
        ];
    }

    private function provideDefaultResults(array $items): Generator
    {
        foreach ($items as $item) {
            $cost = $item['_cost'] ?? [];

            yield $item['_reference'] => Util::cost($cost);

            if (empty($item['children'])) {
                continue;
            }

            yield from $this->provideDefaultResults($item['children']);
        }
    }

    /**
     * @dataProvider provideData()
     */
    public function testCalculateSaleItemAsSingle(array $data, array $subjectCosts): void
    {
        Fixture::order($data);

        $this->configureSubjectCostGuesser($subjectCosts);

        foreach ($this->provideSingleResults($data['items']) as $reference => $expected) {
            $item = Fixture::get($reference);

            $result = $this->itemCostCalculator->calculateSaleItem($item, null, true);

            self::assertEquals($expected, $result, "Unexpected cost for item $reference.");
        }
    }

    private function provideSingleResults(array $items): Generator
    {
        foreach ($items as $item) {
            $cost = $item['_cost']['_single'] ?? $item['_cost'] ?? [];

            yield $item['_reference'] => Util::cost($cost);

            if (empty($item['children'])) {
                continue;
            }

            yield from $this->provideSingleResults($item['children']);
        }
    }

    private function configureSubjectCostGuesser(array $costs): void
    {
        if (empty($costs)) {
            return;
        }

        $map = [];
        foreach ($costs as $reference => $cost) {
            $map[] = [Fixture::get($reference), $cost];
        }

        $this
            ->getPurchaseCostGuesserMock()
            ->method('guess')
            ->willReturnMap($map);
    }
}
