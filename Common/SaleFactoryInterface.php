<?php

namespace Ekyna\Component\Commerce\Common;

/**
 * Interface SaleFactoryInterface
 * @package Ekyna\Component\Commerce\Common
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface SaleFactoryInterface
{
    /**
     * Creates an address regarding to the sale type.
     *
     * @param Model\SaleInterface $sale
     *
     * @return Model\AddressInterface
     */
    public function createAddressForSale(Model\SaleInterface $sale);

    /**
     * Creates a sale item regarding to the sale type.
     *
     * @param Model\SaleInterface $sale
     *
     * @return Model\SaleItemInterface
     */
    public function createItemForSale(Model\SaleInterface $sale);

    /**
     * Creates an adjustment regarding to the sale type.
     *
     * @param Model\SaleInterface $sale
     *
     * @return Model\AdjustmentInterface
     */
    public function createAdjustmentForSale(Model\SaleInterface $sale);

    /**
     * Creates an adjustment regarding to the sale item type.
     *
     * @param Model\SaleItemInterface $item
     *
     * @return Model\AdjustmentInterface
     */
    public function createAdjustmentForSaleItem(Model\SaleItemInterface $item);
}
