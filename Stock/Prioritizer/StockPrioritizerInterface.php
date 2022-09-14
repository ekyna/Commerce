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
     * @param bool                     $sameSale Whether to allow to pick quantity from same sale's assignments.
     *
     * @return bool Whether the sale item has been prioritized.
     */
    public function prioritizeSaleItem(
        Common\SaleItemInterface $item,
        Decimal                  $quantity = null,
        bool                     $sameSale = false
    ): bool;
}
