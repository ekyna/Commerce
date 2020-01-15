<?php declare(strict_types=1);

namespace Ekyna\Component\Commerce\Common\Calculator;

use Ekyna\Component\Commerce\Common\Model;

/**
 * Interface MarginCalculatorInterface
 * @package Ekyna\Component\Commerce\Common\Calculator
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface MarginCalculatorInterface
{
    /**
     * Calculates the sale margin.
     *
     * @param Model\SaleInterface $sale
     * @param string              $currency
     *
     * @return Model\Margin
     */
    public function calculateSale(Model\SaleInterface $sale, string $currency = null): ?Model\Margin;
}
