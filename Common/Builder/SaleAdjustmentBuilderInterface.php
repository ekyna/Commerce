<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Common\Builder;

use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Common\Model\SaleItemInterface;

/**
 * Interface SaleAdjustmentBuilderInterface
 * @package Ekyna\Component\Commerce\Common
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface SaleAdjustmentBuilderInterface
{
    /**
     * Builds the discount adjustments for the given sale.
     *
     * @param SaleInterface $sale
     * @param bool          $persistence
     *
     * @return bool Whether at least one adjustment has been changed.
     */
    public function buildSaleDiscountAdjustments(SaleInterface $sale, bool $persistence = false): bool;

    /**
     * Builds the discount adjustments for the given sale items recursively.
     *
     * @param SaleInterface|SaleItemInterface $parent
     * @param bool                            $persistence
     *
     * @return bool Whether at least one adjustment has been changed.
     */
    public function buildSaleItemsDiscountAdjustments(
        SaleInterface|SaleItemInterface $parent,
        bool                            $persistence = false
    ): bool;

    /**
     * Builds the discount adjustments for the given sale item.
     *
     * @param SaleItemInterface $item
     * @param bool              $persistence
     *
     * @return bool Whether at least one adjustment has been changed.
     */
    public function buildSaleItemDiscountAdjustments(SaleItemInterface $item, bool $persistence = false): bool;

    /**
     * Builds the taxation adjustments for the given sale.
     *
     * @param SaleInterface $sale
     * @param bool          $persistence
     *
     * @return bool Whether at least one adjustment has been changed.
     */
    public function buildSaleTaxationAdjustments(SaleInterface $sale, bool $persistence = false): bool;

    /**
     * Builds the taxation adjustments for the given sale items recursively.
     *
     * @param SaleInterface|SaleItemInterface $parent
     * @param bool                            $persistence
     *
     * @return bool Whether at least one adjustment has been changed.
     */
    public function buildSaleItemsTaxationAdjustments(
        SaleInterface|SaleItemInterface $parent,
        bool                            $persistence = false
    ): bool;

    /**
     * Builds the taxation adjustments for the given sale item.
     *
     * @param SaleItemInterface $item
     * @param bool              $persistence
     *
     * @return bool Whether at least one adjustment has been changed.
     */
    public function buildSaleItemTaxationAdjustments(SaleItemInterface $item, bool $persistence = false): bool;

    /**
     * Makes all sale's discount adjustments mutable.
     */
    public function makeSaleDiscountsMutable(SaleInterface $sale): void;

    /**
     * Clears all sale's mutable discount adjustments.
     */
    public function clearSaleMutableDiscounts(SaleInterface $sale): void;
}
