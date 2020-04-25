<?php

namespace Ekyna\Component\Commerce\Supplier\Model;

use Ekyna\Component\Commerce\Stock\Model\StockUnitInterface;
use Ekyna\Component\Commerce\Subject\Model\SubjectRelativeInterface;

/**
 * Class SupplierOrderItemInterface
 * @package Ekyna\Component\Commerce\Supplier\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface SupplierOrderItemInterface extends SubjectRelativeInterface
{
    /**
     * Returns the supplier order.
     *
     * @return SupplierOrderInterface
     */
    public function getOrder(): ?SupplierOrderInterface;

    /**
     * Sets the supplier order.
     *
     * @param SupplierOrderInterface $order
     *
     * @return $this|SupplierOrderItemInterface
     */
    public function setOrder(SupplierOrderInterface $order = null): SupplierOrderItemInterface;

    /**
     * Returns the supplier product.
     *
     * @return SupplierProductInterface
     */
    public function getProduct(): ?SupplierProductInterface;

    /**
     * Sets the supplier product.
     *
     * @param SupplierProductInterface $product
     *
     * @return $this|SupplierOrderItemInterface
     */
    public function setProduct(SupplierProductInterface $product = null): SupplierOrderItemInterface;

    /**
     * Returns the stock unit.
     *
     * @return StockUnitInterface
     */
    public function getStockUnit(): ?StockUnitInterface;

    /**
     * Sets the stock unit.
     *
     * @param StockUnitInterface $stockUnit
     *
     * @return $this|SupplierOrderItemInterface
     */
    public function setStockUnit(StockUnitInterface $stockUnit): SupplierOrderItemInterface;

    /**
     * Returns the quantity.
     *
     * @return float
     */
    public function getQuantity(): float;

    /**
     * Sets the quantity.
     *
     * @param float $quantity
     *
     * @return $this|SupplierOrderItemInterface
     */
    public function setQuantity(float $quantity): SupplierOrderItemInterface;
}
