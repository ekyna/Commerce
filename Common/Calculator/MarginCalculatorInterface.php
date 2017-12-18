<?php declare(strict_types=1);

namespace Ekyna\Component\Commerce\Common\Calculator;

use Ekyna\Component\Commerce\Common\Model;

/**
 * Interface MarginCalculatorInterface
 * @package Ekyna\Component\Commerce\Common\Calculator
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface MarginCalculatorInterface
{
    /**
     * Calculates the sale margin.
     *
     * @param Model\SaleInterface $sale
     *
     * @return Margin
     */
    public function calculateSale(Model\SaleInterface $sale): ?Margin;

    /**
     * Calculates the sale item margin.
     *
     * @param Model\SaleItemInterface $item
     *
     * @return Margin|null
     */
    public function calculateSaleItem(Model\SaleItemInterface $item): ?Margin;

    /**
     * Calculates the sale shipment margin.
     *
     * @param Model\SaleInterface $sale
     *
     * @return Margin
     */
    public function calculateSaleShipment(Model\SaleInterface $sale): Margin;
}
