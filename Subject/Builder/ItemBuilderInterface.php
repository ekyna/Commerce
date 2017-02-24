<?php

namespace Ekyna\Component\Commerce\Subject\Builder;

use Ekyna\Component\Commerce\Common\Model\SaleItemInterface;

/**
 * Interface ItemBuilderInterface
 * @package Ekyna\Component\Commerce\Subject\Builder
 * @author  Etienne Dauvergne <contact@ekyna.com>
 *
 * @deprecated Use event system
 */
interface ItemBuilderInterface
{
    /**
     * Initializes the item (assign subjects recursively).
     *
     * @param SaleItemInterface $item
     *
     * @deprecated Use event system
     */
    public function initializeItem(SaleItemInterface $item);

    /**
     * Builds the item regarding to the subject data.
     *
     * @param SaleItemInterface $item
     *
     * @deprecated Use event system
     */
    public function buildItem(SaleItemInterface $item);

    /**
     * Returns the applicable adjustment data (discount) for the given sale item.
     *
     * @param SaleItemInterface $item
     *
     * @return \Ekyna\Component\Commerce\Common\Model\AdjustmentData[]
     *
     * @deprecated Use event system
     */
    public function buildAdjustmentsData(SaleItemInterface $item);
}
