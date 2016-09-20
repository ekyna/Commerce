<?php

namespace Ekyna\Component\Commerce\Common\View;

use Ekyna\Component\Commerce\Common\Model;

/**
 * Interface ViewVarsBuilderInterface
 * @package Ekyna\Component\Commerce\Common\View
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface ViewVarsBuilderInterface
{
    /**
     * Builds the sale view vars.
     *
     * @param Model\SaleInterface $sale
     * @param array               $options
     *
     * @return array
     */
    public function buildSaleViewVars(Model\SaleInterface $sale, array $options = []);

    /**
     * Builds the sale item view vars.
     *
     * @param Model\SaleItemInterface $item
     * @param array                   $options
     *
     * @return array
     */
    public function buildItemViewVars(Model\SaleItemInterface $item, array $options = []);

    /**
     * Builds the adjustment view vars.
     *
     * @param Model\AdjustmentInterface $adjustment
     * @param array                     $options
     *
     * @return array
     */
    public function buildAdjustmentViewVars(Model\AdjustmentInterface $adjustment, array $options = []);
}
