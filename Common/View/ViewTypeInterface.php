<?php

namespace Ekyna\Component\Commerce\Common\View;

use Ekyna\Component\Commerce\Common\Model;
use Ekyna\Component\Commerce\Common\Util\Formatter;

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
    public function setFormatter(Formatter $formatter): void;

    /**
     * Configures the builder options.
     *
     * @param Model\SaleInterface $sale
     * @param SaleView            $view
     * @param array               $options
     */
    public function configureOptions(Model\SaleInterface $sale, SaleView $view, array &$options): void;

    /**
     * Builds the sale view.
     *
     * @param Model\SaleInterface $sale
     * @param SaleView            $view
     * @param array               $options
     */
    public function buildSaleView(Model\SaleInterface $sale, SaleView $view, array $options): void;

    /**
     * Builds the sale item view.
     *
     * @param Model\SaleItemInterface $item
     * @param LineView                $view
     * @param array                   $options
     */
    public function buildItemView(Model\SaleItemInterface $item, LineView $view, array $options): void;

    /**
     * Builds the adjustment view.
     *
     * @param Model\AdjustmentInterface $adjustment
     * @param LineView                  $view
     * @param array                     $options
     */
    public function buildAdjustmentView(Model\AdjustmentInterface $adjustment, LineView $view, array $options): void;

    /**
     * Builds the shipment view.
     *
     * @param Model\SaleInterface $sale
     * @param LineView            $view
     * @param array               $options
     */
    public function buildShipmentView(Model\SaleInterface $sale, LineView $view, array $options): void;

    /**
     * Returns whether the vars builder supports the given sale.
     *
     * @param Model\SaleInterface $sale
     *
     * @return bool
     */
    public function supportsSale(Model\SaleInterface $sale): bool;

    /**
     * Returns the name.
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Returns the priority.
     *
     * @return int
     */
    public function getPriority(): int;
}
