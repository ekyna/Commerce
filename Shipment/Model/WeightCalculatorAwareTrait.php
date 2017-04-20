<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Shipment\Model;

use Ekyna\Component\Commerce\Shipment\Calculator\WeightCalculatorInterface;

/**
 * Trait WeightCalculatorAwareTrait
 * @package Ekyna\Component\Commerce\Shipment\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
trait WeightCalculatorAwareTrait
{
    protected WeightCalculatorInterface $weightCalculator;


    public function setWeightCalculator(WeightCalculatorInterface $weightCalculator): void
    {
        $this->weightCalculator = $weightCalculator;
    }

    public function getWeightCalculator(): WeightCalculatorInterface
    {
        return $this->weightCalculator;
    }
}
