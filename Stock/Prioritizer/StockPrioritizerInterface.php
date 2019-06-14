<?php

namespace Ekyna\Component\Commerce\Stock\Prioritizer;

use Ekyna\Component\Commerce\Common\Model as Common;

/**
 * Interface StockPrioritizerInterface
 * @package Ekyna\Component\Commerce\Stock\Prioritizer
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface StockPrioritizerInterface
{
    /**
     * Returns whether or not the sale can be prioritized.
     *
     * @param Common\SaleInterface $sale
     *
     * @return bool
     */
    public function canPrioritizeSale(Common\SaleInterface $sale);

    /**
     * Returns whether or not the sale item can be prioritized.
     *
     * @param Common\SaleItemInterface $item
     * @param bool                     $checkSale Whether to check if sale can be prioritized.
     *
     * @return bool
     */
    public function canPrioritizeSaleItem(Common\SaleItemInterface $item, bool $checkSale = true);

    /**
     * Prioritizes a sale by trying to move stock assignments
     * to make all items shippable.
     *
     * @param Common\SaleInterface $sale
     *
     * @return bool Whether or not the sale has been prioritized.
     */
    public function prioritizeSale(Common\SaleInterface $sale);

    /**
     * Prioritizes a sale item recursively
     * by trying to move stock assignments
     * to make the item and its children shippable.
     *
     * @param Common\SaleItemInterface $item
     * @param bool                     $checkSale Whether to check if sale can be prioritized.
     *
     * @return bool Whether or not the sale item has been prioritized.
     */
    public function prioritizeSaleItem(Common\SaleItemInterface $item, bool $checkSale = true);
}