<?php

declare(strict_types=1);

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
     * @return Model\AdjustmentDataInterface[]
     */
    public function resolveSale(Model\SaleInterface $sale): array;

    /**
     * Resolves the sale item discount adjustments.
     *
     * @return Model\AdjustmentDataInterface[]
     */
    public function resolveSaleItem(Model\SaleItemInterface $item): array;
}
