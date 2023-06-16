<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Common\Calculator;

use Ekyna\Component\Commerce\Common\Model\Margin;
use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Common\Model\SaleItemInterface;

/**
 * Interface MarginCalculatorInterface
 * @package Ekyna\Component\Commerce\Common\Calculator
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface MarginCalculatorInterface
{
    /**
     * Calculates the sale margin.
     */
    public function calculateSale(SaleInterface $sale): Margin;

    /**
     * Calculates the sale item margin.
     */
    public function calculateSaleItem(SaleItemInterface $item, bool $single = false): Margin;

    /**
     * Calculates the sale shipment margin.
     */
    public function calculateSaleShipment(SaleInterface $sale): Margin;
}
