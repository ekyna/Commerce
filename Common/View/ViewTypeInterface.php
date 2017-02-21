<?php

namespace Ekyna\Component\Commerce\Common\View;

use Ekyna\Component\Commerce\Common\Model;

/**
 * Interface ViewVarsBuilderInterface
 * @package Ekyna\Component\Commerce\Common\View
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface ViewTypeInterface
{
    /**
     * Builds the sale view.
     *
     * @param Model\SaleInterface $sale
     * @param AbstractView        $view
     * @param Model\SaleInterface $sale
     * @param array               $options
     */
    public function buildSaleView(Model\SaleInterface $sale, AbstractView $view, array $options);

    /**
     * Builds the sale item view.
     *
     * @param Model\SaleItemInterface $item
     * @param AbstractView            $view
     * @param array                   $options
     */
    public function buildItemView(Model\SaleItemInterface $item, AbstractView $view, array $options);

    /**
     * Builds the adjustment view.
     *
     * @param Model\AdjustmentInterface $adjustment
     * @param AbstractView              $view
     * @param array                     $options
     */
    public function buildAdjustmentView(Model\AdjustmentInterface $adjustment, AbstractView $view, array $options);

    /**
     * Builds the shipment view.
     *
     * @param Model\SaleInterface $sale
     * @param AbstractView        $view
     * @param array               $options
     */
    public function buildShipmentView(Model\SaleInterface $sale, AbstractView $view, array $options);

    /**
     * Returns whether the vars builder supports the given sale.
     *
     * @param Model\SaleInterface $sale
     *
     * @return bool
     */
    public function supportsSale(Model\SaleInterface $sale);

    /**
     * Returns the name.
     *
     * @return string
     */
    public function getName();

}
