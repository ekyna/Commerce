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
     * Sets the formatter.
     *
     * @param Formatter $formatter
     */
    public function setFormatter(Formatter $formatter);

    /**
     * Builds the sale view.
     *
     * @param Model\SaleInterface $sale
     * @param SaleView            $view
     * @param Model\SaleInterface $sale
     * @param array               $options
     */
    public function buildSaleView(Model\SaleInterface $sale, SaleView $view, array $options);

    /**
     * Builds the sale item view.
     *
     * @param Model\SaleItemInterface $item
     * @param LineView                $view
     * @param array                   $options
     */
    public function buildItemView(Model\SaleItemInterface $item, LineView $view, array $options);

    /**
     * Builds the adjustment view.
     *
     * @param Model\AdjustmentInterface $adjustment
     * @param LineView                  $view
     * @param array                     $options
     */
    public function buildAdjustmentView(Model\AdjustmentInterface $adjustment, LineView $view, array $options);

    /**
     * Builds the shipment view.
     *
     * @param Model\SaleInterface $sale
     * @param LineView            $view
     * @param array               $options
     */
    public function buildShipmentView(Model\SaleInterface $sale, LineView $view, array $options);

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
