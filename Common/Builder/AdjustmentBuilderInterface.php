<?php

declare(strict_types=1);

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
     * @return bool Whether at least one adjustment has been changed.
     */
    public function buildSaleDiscountAdjustments(Model\SaleInterface $sale, bool $persistence = false): bool;

    /**
     * Builds the discount adjustments for the given sale items recursively.
     *
     * @param Model\SaleInterface|Model\SaleItemInterface $parent
     * @param bool                                        $persistence
     *
     * @return bool Whether at least one adjustment has been changed.
     */
    public function buildSaleItemsDiscountAdjustments($parent, bool $persistence = false): bool;

    /**
     * Builds the discount adjustments for the given sale item.
     *
     * @param Model\SaleItemInterface $item
     * @param bool                    $persistence
     *
     * @return bool Whether at least one adjustment has been changed.
     */
    public function buildSaleItemDiscountAdjustments(Model\SaleItemInterface $item, bool $persistence = false): bool;

    /**
     * Builds the taxation adjustments for the given sale.
     *
     * @param Model\SaleInterface $sale
     * @param bool                $persistence
     *
     * @return bool Whether at least one adjustment has been changed.
     */
    public function buildSaleTaxationAdjustments(Model\SaleInterface $sale, bool $persistence = false): bool;

    /**
     * Builds the taxation adjustments for the given sale items recursively.
     *
     * @param Model\SaleInterface|Model\SaleItemInterface $parent
     * @param bool                                        $persistence
     *
     * @return bool Whether at least one adjustment has been changed.
     */
    public function buildSaleItemsTaxationAdjustments($parent, bool $persistence = false): bool;

    /**
     * Builds the taxation adjustments for the given sale item.
     *
     * @param Model\SaleItemInterface $item
     * @param bool                    $persistence
     *
     * @return bool Whether at least one adjustment has been changed.
     */
    public function buildSaleItemTaxationAdjustments(Model\SaleItemInterface $item, bool $persistence = false): bool;

    /**
     * Makes all sale's discount adjustments mutable.
     */
    public function makeSaleDiscountsMutable(Model\SaleInterface $sale): void;

    /**
     * Clears all sale's mutable discount adjustments.
     */
    public function clearSaleMutableDiscounts(Model\SaleInterface $sale): void;
}
