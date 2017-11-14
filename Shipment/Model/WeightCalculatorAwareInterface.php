<?php

namespace Ekyna\Component\Commerce\Shipment\Model;

use Ekyna\Component\Commerce\Shipment\Calculator\WeightCalculatorInterface;

/**
 * Interface WeightCalculatorAwareInterface
 * @package Ekyna\Component\Commerce\Shipment\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface WeightCalculatorAwareInterface
{
    /**
     * Returns the weight calculator.
     *
     * @return WeightCalculatorInterface
     */
    public function getWeightCalculator();

    /**
     * Sets the weight calculator.
     *
     * @param WeightCalculatorInterface $weightCalculator
     */
    public function setWeightCalculator(WeightCalculatorInterface $weightCalculator);
}
