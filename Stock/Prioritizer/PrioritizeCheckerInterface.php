<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Stock\Prioritizer;

use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Common\Model\SaleItemInterface;

/**
 * Interface PrioritizeCheckerInterface
 * @package Ekyna\Component\Commerce\Stock\Prioritizer
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
interface PrioritizeCheckerInterface
{
    /**
     * Returns whether the sale can be prioritized.
     */
    public function canPrioritizeSale(SaleInterface $sale): bool;

    /**
     * Returns whether the sale item can be prioritized.
     */
    public function canPrioritizeSaleItem(SaleItemInterface $item): bool;
}
