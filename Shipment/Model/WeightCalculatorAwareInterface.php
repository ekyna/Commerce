<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Shipment\Model;

use Ekyna\Component\Commerce\Shipment\Calculator\WeightCalculatorInterface;

/**
 * Interface WeightCalculatorAwareInterface
 * @package Ekyna\Component\Commerce\Shipment\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface WeightCalculatorAwareInterface
{
    public function getWeightCalculator(): WeightCalculatorInterface;

    public function setWeightCalculator(WeightCalculatorInterface $weightCalculator): void;
}
