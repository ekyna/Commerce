<?php

namespace Ekyna\Component\Commerce\Common\Resolver;

use Ekyna\Component\Commerce\Common\Model;

/**
 * Class DiscountResolverInterface
 * @package Ekyna\Component\Commerce\Common\Resolver
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface DiscountResolverInterface
{
    /**
     * Resolves the sale discount adjustments.
     *
     * @param Model\SaleInterface $sale
     *
     * @return array|Model\AdjustmentDataInterface[]
     */
    public function resolveSale(Model\SaleInterface $sale);

    /**
     * Resolves the sale item discount adjustments.
     *
     * @param Model\SaleItemInterface $item
     *
     * @return array|Model\AdjustmentDataInterface[]
     */
    public function resolveSaleItem(Model\SaleItemInterface $item);
}
