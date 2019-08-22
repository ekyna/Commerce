<?php

namespace Ekyna\Component\Commerce\Common\Model;

/**
 * Interface SaleAdjustmentInterface
 * @package Ekyna\Component\Commerce\Common\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface SaleAdjustmentInterface extends AdjustmentInterface
{
    /**
     * Returns the sale.
     *
     * @return SaleInterface
     */
    public function getSale();

    /**
     * Clears the results.
     *
     * @return $this|SaleAdjustmentInterface
     *
     * @internal Usage reserved to calculator.
     */
    public function clearResults(): SaleAdjustmentInterface;

    /**
     * Sets the result.
     *
     * @param Amount $result
     *
     * @return $this|SaleAdjustmentInterface
     *
     * @internal Usage reserved to calculator.
     */
    public function setResult(Amount $result): SaleAdjustmentInterface;

    /**
     * Returns the result for the given currency.
     *
     * @param string $currency
     *
     * @return Amount
     *
     * @internal Usage reserved to view builder.
     */
    public function getResult(string $currency): ?Amount;
}
