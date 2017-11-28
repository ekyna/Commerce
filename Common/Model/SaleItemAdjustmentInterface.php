<?php

namespace Ekyna\Component\Commerce\Common\Model;

/**
 * Interface SaleItemAdjustmentInterface
 * @package Ekyna\Component\Commerce\Common\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface SaleItemAdjustmentInterface extends AdjustmentInterface
{
    /**
     * Returns the sale item.
     *
     * @return SaleItemInterface
     */
    public function getItem();
}
