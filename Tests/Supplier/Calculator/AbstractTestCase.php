<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Tests\Supplier\Calculator;

use Decimal\Decimal;
use Ekyna\Component\Commerce\Supplier\Calculator\WeightingCalculatorInterface;
use Ekyna\Component\Commerce\Supplier\Model\ItemWeighting;
use Ekyna\Component\Commerce\Tests\Fixture;
use Ekyna\Component\Commerce\Tests\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Class AbstractTestCase
 * @package Ekyna\Component\Commerce\Tests\Supplier\Calculator
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
class AbstractTestCase extends TestCase
{
    protected MockObject|WeightingCalculatorInterface|null $weightingCalculator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->weightingCalculator = $this->createMock(WeightingCalculatorInterface::class);
    }

    protected function tearDown(): void
    {
        $this->weightingCalculator = null;

        parent::tearDown();
    }

    /**
     * Configures the supplier order weighting calculator.
     *
     * @param array $data
     * @return void
     */
    protected function configureWeightingCalculator(array $data): void
    {
        $map = [];

        foreach ($data['items'] as $datum) {
            $item = Fixture::get($datum['_reference']);

            $map[] = [
                $item,
                new ItemWeighting(
                    new Decimal((string)$datum['_weighting']['weight']),
                    new Decimal((string)$datum['_weighting']['price']),
                    new Decimal((string)($datum['_weighting']['quantity'] ?? 1)),
                ),
            ];
        }

        $this
            ->weightingCalculator
            ->method('getWeighting')
            ->willReturnMap($map);
    }
}
