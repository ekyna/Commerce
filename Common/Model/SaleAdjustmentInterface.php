<?php

namespace Ekyna\Component\Commerce\Common\Model;

use Ekyna\Component\Commerce\Common\Calculator\Amount;

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
     * Clears the result.
     *
     * @return $this|SaleAdjustmentInterface
     *
     * @internal Usage reserved to calculator.
     */
    public function clearResult();

    /**
     * Sets the result.
     *
     * @param Amount $result
     *
     * @internal Usage reserved to calculator.
     */
    public function setResult(Amount $result);

    /**
     * Returns the result.
     *
     * @return Amount
     *
     * @internal Usage reserved to view builder.
     */
    public function getResult();
}
