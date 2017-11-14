<?php

namespace Ekyna\Component\Commerce\Shipment\Model;

use Ekyna\Component\Commerce\Shipment\Calculator\WeightCalculatorInterface;

/**
 * Trait WeightCalculatorAwareTrait
 * @package Ekyna\Component\Commerce\Shipment\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
trait WeightCalculatorAwareTrait
{
    /**
     * @var WeightCalculatorInterface
     */
    protected $weightCalculator;


    /**
     * Returns the weight calculator.
     *
     * @return WeightCalculatorInterface
     */
    public function getWeightCalculator()
    {
        return $this->weightCalculator;
    }

    /**
     * Sets the weight calculator.
     *
     * @param WeightCalculatorInterface $weightCalculator
     */
    public function setWeightCalculator(WeightCalculatorInterface $weightCalculator)
    {
        $this->weightCalculator = $weightCalculator;
    }
}
