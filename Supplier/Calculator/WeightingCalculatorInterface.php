<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Supplier\Calculator;

use Ekyna\Component\Commerce\Exception\LogicException;
use Ekyna\Component\Commerce\Supplier\Model\ItemWeighting;
use Ekyna\Component\Commerce\Supplier\Model\SupplierOrderItemInterface;

/**
 * Interface WeightingCalculatorInterface
 * @package Ekyna\Component\Commerce\Supplier\Calculator
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
interface WeightingCalculatorInterface
{
    /**
     * Returns the supplier order item weighting.
     *
     * @throws LogicException
     */
    public function getWeighting(SupplierOrderItemInterface $item): ItemWeighting;
}
