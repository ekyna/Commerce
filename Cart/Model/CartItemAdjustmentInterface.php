<?php

namespace Ekyna\Component\Commerce\Cart\Model;

use Ekyna\Component\Commerce\Common\Model\SaleItemAdjustmentInterface;

/**
 * Interface CartItemAdjustmentInterface
 * @package Ekyna\Component\Commerce\Cart\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface CartItemAdjustmentInterface extends SaleItemAdjustmentInterface
{
    /**
     * Sets the cart item.
     *
     * @param CartItemInterface $item
     *
     * @return $this|CartAdjustmentInterface
     */
    public function setItem(CartItemInterface $item = null);
}
