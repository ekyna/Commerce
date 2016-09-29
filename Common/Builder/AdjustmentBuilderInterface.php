<?php

namespace Ekyna\Component\Commerce\Common\Builder;

use Ekyna\Component\Commerce\Common\Model;
use Ekyna\Component\Commerce\Pricing\Model\TaxableInterface;

/**
 * Interface AdjustmentBuilderInterface
 * @package Ekyna\Component\Commerce\Common
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface AdjustmentBuilderInterface
{
    /**
     * Builds the taxation adjustments for the sale item.
     *
     * @param Model\SaleItemInterface $item
     * @param TaxableInterface        $taxable
     */
    public function buildTaxationAdjustmentsForSaleItem(
        Model\SaleItemInterface $item,
        TaxableInterface $taxable = null
    );
}
