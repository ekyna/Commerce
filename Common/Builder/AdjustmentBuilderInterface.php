<?php

namespace Ekyna\Component\Commerce\Common\Builder;

use Ekyna\Component\Commerce\Common\Model;

/**
 * Interface AdjustmentBuilderInterface
 * @package Ekyna\Component\Commerce\Common
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface AdjustmentBuilderInterface
{
    /**
     * Builds the discount adjustments for the given sale.
     *
     * @param Model\SaleInterface $sale
     * @param bool                $persistence
     *
     * @return bool Whether at least one adjustment has been changed (created, updated or deleted)
     */
    public function buildDiscountAdjustmentsForSale(Model\SaleInterface $sale, $persistence = false);

    /**
     * Builds the discount adjustments for the given sale items recursively.
     *
     * @param Model\SaleInterface|Model\SaleItemInterface $parent
     * @param bool                                        $persistence
     *
     * @return bool Whether at least one adjustment has been changed (created, updated or deleted)
     */
    public function buildDiscountAdjustmentsForSaleItems($parent, $persistence = false);

    /**
     * Builds the discount adjustments for the given sale item.
     *
     * @param Model\SaleItemInterface $item
     * @param bool                    $persistence
     *
     * @return bool Whether at least one adjustment has been changed (created, updated or deleted)
     */
    public function buildDiscountAdjustmentsForSaleItem(Model\SaleItemInterface $item, $persistence = false);

    /**
     * Builds the taxation adjustments for the given sale.
     *
     * @param Model\SaleInterface $sale
     * @param bool                $persistence
     *
     * @return bool Whether at least one adjustment has been changed (created, updated or deleted)
     */
    public function buildTaxationAdjustmentsForSale(Model\SaleInterface $sale, $persistence = false);

    /**
     * Builds the taxation adjustments for the given sale items recursively.
     *
     * @param Model\SaleInterface|Model\SaleItemInterface $parent
     * @param bool                                        $persistence
     *
     * @return bool Whether at least one adjustment has been changed (created, updated or deleted)
     */
    public function buildTaxationAdjustmentsForSaleItems($parent, $persistence = false);

    /**
     * Builds the taxation adjustments for the given sale item.
     *
     * @param Model\SaleItemInterface $item
     * @param bool                    $persistence
     *
     * @return bool Whether at least one adjustment has been changed (created, updated or deleted)
     */
    public function buildTaxationAdjustmentsForSaleItem(Model\SaleItemInterface $item, $persistence = false);
}
