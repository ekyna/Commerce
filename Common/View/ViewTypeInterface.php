<?php

declare(strict_types=1);

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
     */
    public function setFormatter(Formatter $formatter): void;

    /**
     * Configures the builder options.
     */
    public function configureOptions(Model\SaleInterface $sale, SaleView $view, array &$options): void;

    /**
     * Builds the sale view.
     */
    public function buildSaleView(Model\SaleInterface $sale, SaleView $view, array $options): void;

    /**
     * Builds the sale item view.
     */
    public function buildItemView(Model\SaleItemInterface $item, LineView $view, array $options): void;

    /**
     * Builds the adjustment view.
     */
    public function buildAdjustmentView(Model\AdjustmentInterface $adjustment, LineView $view, array $options): void;

    /**
     * Builds the shipment view.
     */
    public function buildShipmentView(Model\SaleInterface $sale, LineView $view, array $options): void;

    /**
     * Returns whether the vars builder supports the given sale.
     */
    public function supportsSale(Model\SaleInterface $sale): bool;

    public function getName(): string;

    public function getPriority(): int;
}
