<?php

namespace Ekyna\Component\Commerce\Common\View;

use Ekyna\Component\Commerce\Common\Model;
use Ekyna\Component\Commerce\Common\Util\Formatter;

/**
 * Class AbstractViewType
 * @package Ekyna\Component\Commerce\Common\View
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
abstract class AbstractViewType implements ViewTypeInterface
{
    /**
     * @var Formatter
     */
    protected $formatter;


    /**
     * @inheritDoc
     */
    public function setFormatter(Formatter $formatter): void
    {
        $this->formatter = $formatter;
    }

    /**
     * @inheritDoc
     */
    public function configureOptions(Model\SaleInterface $sale, SaleView $view, array &$options): void
    {

    }

    /**
     * @inheritDoc
     */
    public function buildSaleView(Model\SaleInterface $sale, SaleView $view, array $options): void
    {

    }

    /**
     * @inheritDoc
     */
    public function buildItemView(Model\SaleItemInterface $item, LineView $view, array $options): void
    {

    }

    /**
     * @inheritDoc
     */
    public function buildAdjustmentView(Model\AdjustmentInterface $adjustment, LineView $view, array $options): void
    {

    }

    /**
     * @inheritDoc
     */
    public function buildShipmentView(Model\SaleInterface $sale, LineView $view, array $options): void
    {

    }

    /**
     * @inheritDoc
     */
    public function getPriority(): int
    {
        return 0;
    }
}
