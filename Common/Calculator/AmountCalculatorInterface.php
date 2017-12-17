<?php declare(strict_types=1);

namespace Ekyna\Component\Commerce\Common\Calculator;

use Ekyna\Component\Commerce\Common\Model;

/**
 * Interface AmountCalculatorInterface
 * @package Ekyna\Component\Commerce\Common\Calculator
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface AmountCalculatorInterface
{
    /**
     * Sets whether to return the cached results.
     *
     * @param bool $cache
     */
    public function setCache(bool $cache);

    /**
     * Calculates the sale amounts.
     *
     * @param Model\SaleInterface $sale The sale
     *
     * @return Amount The sale calculation result
     */
    public function calculateSale(Model\SaleInterface $sale): Amount;

    /**
     * Calculates the sale item amounts.
     *
     * @param Model\SaleItemInterface $item     The sale item
     * @param float                   $quantity The overridden quantity (optional)
     *
     * @return Amount The sale item calculation result
     *
     * @throws \Ekyna\Component\Commerce\Exception\LogicException
     *         If a private has discount adjustment or if a private item does not share the same tax group as its parent
     */
    public function calculateSaleItem(Model\SaleItemInterface $item, float $quantity = null): Amount;

    /**
     * Calculates the sale discount result.
     *
     * @param Model\SaleAdjustmentInterface $adjustment The sale discount adjustment
     * @param Amount                        $gross      The gross result (items sum)
     * @param Amount                        $final      The gross result (items sum)
     *
     * @return Amount The sale discount calculation result
     * @throws \Ekyna\Component\Commerce\Exception\LogicException
     */
    public function calculateSaleDiscount(Model\SaleAdjustmentInterface $adjustment, Amount $gross, Amount $final): Amount;

    /**
     * Calculates the shipment result.
     *
     * @param Model\SaleInterface $sale  The sale
     * @param Amount              $final The final result to add the shipment result to.
     *
     * @return Amount The sale shipment calculation result
     */
    public function calculateSaleShipment(Model\SaleInterface $sale, Amount $final): ?Amount;
}
