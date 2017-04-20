<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Stock\Prioritizer;

use Decimal\Decimal;
use Ekyna\Component\Commerce\Common\Model as Common;

/**
 * Interface StockPrioritizerInterface
 * @package Ekyna\Component\Commerce\Stock\Prioritizer
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface StockPrioritizerInterface
{
    /**
     * Returns whether the sale can be prioritized.
     */
    public function canPrioritizeSale(Common\SaleInterface $sale): bool;

    /**
     * Returns whether the sale item can be prioritized.
     *
     * @param Common\SaleItemInterface $item
     * @param bool                     $checkSale Whether to check if sale can be prioritized.
     *
     * @return bool
     */
    public function canPrioritizeSaleItem(Common\SaleItemInterface $item, bool $checkSale = true): bool;

    /**
     * Prioritizes a sale by trying to move stock assignments
     * to make all items shippable.
     *
     * @return bool Whether the sale has been prioritized.
     */
    public function prioritizeSale(Common\SaleInterface $sale): bool;

    /**
     * Prioritizes a sale item recursively
     * by trying to move stock assignments
     * to make the item and its children shippable.
     *
     * @param Common\SaleItemInterface $item
     * @param Decimal|null             $quantity  The quantity to prioritize
     * @param bool                     $checkSale Whether to check if sale can be prioritized.
     *
     * @return bool Whether the sale item has been prioritized.
     */
    public function prioritizeSaleItem(
        Common\SaleItemInterface $item,
        Decimal                  $quantity = null,
        bool                     $checkSale = true
    ): bool;
}
