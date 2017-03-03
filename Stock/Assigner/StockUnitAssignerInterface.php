<?php

namespace Ekyna\Component\Commerce\Stock\Assigner;

use Ekyna\Component\Commerce\Common\Model\SaleItemInterface;

/**
 * Interface StockUnitAssignerInterface
 * @package Ekyna\Component\Commerce\Stock\Assigner
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface StockUnitAssignerInterface
{
    /**
     * Creates the stock assignments to the given sale item.
     *
     * @param SaleItemInterface $item
     */
    public function createAssignments(SaleItemInterface $item);

    /**
     * Dispatch the quantity change on sale item stock assignments.
     *
     * @param SaleItemInterface $item
     * @param float             $deltaQuantity The difference between the new and old quantity.
     */
    public function dispatchQuantityChange(SaleItemInterface $item, $deltaQuantity);

    /**
     * Removes the stock assignments from the given sale item.
     *
     * @param SaleItemInterface $item
     */
    public function removeAssignments(SaleItemInterface $item);
}
