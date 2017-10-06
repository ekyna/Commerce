<?php

namespace Ekyna\Component\Commerce\Stock\Linker;

use Ekyna\Component\Commerce\Stock\Model\StockUnitInterface;
use Ekyna\Component\Commerce\Supplier\Model\SupplierOrderItemInterface;

/**
 * Interface StockUnitLinkerInterface
 * @package Ekyna\Component\Commerce\Stock\Linker
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface StockUnitLinkerInterface
{
    /**
     * Link the given supplier order item to new stock unit.
     *
     * @param SupplierOrderItemInterface $supplierOrderItem
     *
     * @throws \Ekyna\Component\Commerce\Exception\LogicException
     */
    public function linkItem(SupplierOrderItemInterface $supplierOrderItem);

    /**
     * Dispatches the ordered quantity change over assignments.
     *
     * @param SupplierOrderItemInterface $supplierOrderItem
     *
     * @throws \Ekyna\Component\Commerce\Exception\LogicException
     */
    public function applyItem(SupplierOrderItemInterface $supplierOrderItem);

    /**
     * Unlink the given supplier order item from its stock unit.
     *
     * @param SupplierOrderItemInterface $supplierOrderItem
     *
     * @throws \Ekyna\Component\Commerce\Exception\LogicException
     */
    public function unlinkItem(SupplierOrderItemInterface $supplierOrderItem);

    /**
     * Updates the stock unit price.
     *
     * @param StockUnitInterface $stockUnit
     *
     * @throws \Ekyna\Component\Commerce\Exception\LogicException
     */
    public function updatePrice(StockUnitInterface $stockUnit);
}
