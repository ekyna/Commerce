<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Report\Util;

use Ekyna\Component\Commerce\Common\Calculator\MarginCalculatorFactory;
use Ekyna\Component\Commerce\Common\Calculator\MarginCalculatorInterface;
use Ekyna\Component\Commerce\Common\Model\Margin;
use Ekyna\Component\Commerce\Report\Section\Model\OrderData;

/**
 * Class OrderUtil
 * @package Ekyna\Component\Commerce\Report\Util
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class OrderUtil
{
    private MarginCalculatorInterface $grossCalculator;
    private MarginCalculatorInterface $commercialCalculator;

    public function __construct(
        private readonly MarginCalculatorFactory $marginCalculatorFactory,
        private readonly string $defaultCurrency
    ) {
    }

    public function clear(): void
    {
        $this->grossCalculator = $this->marginCalculatorFactory->create(profit: true);
        $this->commercialCalculator = $this->marginCalculatorFactory->create();
    }

    public function getGrossCalculator(): MarginCalculatorInterface
    {
        return $this->grossCalculator;
    }

    public function getCommercialCalculator(): MarginCalculatorInterface
    {
        return $this->commercialCalculator;
    }

    public function create(): OrderData
    {
        return new OrderData(
            new Margin($this->defaultCurrency),
            new Margin($this->defaultCurrency)
        );
    }
}
