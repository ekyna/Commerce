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

    /**
     * Sets the sale item.
     *
     * @param SaleItemInterface|null $item
     *
     * @return $this|SaleItemAdjustmentInterface
     */
    public function setItem(SaleItemInterface $item = null);
}
