<?php declare(strict_types=1);

namespace Ekyna\Component\Commerce\Common\Calculator;

use Ekyna\Component\Commerce\Common\Currency\CurrencyConverterInterface;
use Ekyna\Component\Commerce\Common\Model;

/**
 * Interface AmountCalculatorInterface
 * @package Ekyna\Component\Commerce\Common\Calculator
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface AmountCalculatorInterface
{
    /**
     * Returns the currency converter.
     *
     * @return CurrencyConverterInterface
     */
    public function getCurrencyConverter(): CurrencyConverterInterface;

    /**
     * Returns the default currency.
     *
     * @return string
     */
    public function getDefaultCurrency(): string;

    /**
     * Calculates the sale amounts.
     *
     * @param Model\SaleInterface $sale     The sale
     * @param string              $currency The result currency
     * @param bool                $cache    Whether to use cached result
     *
     * @return Model\Amount The sale calculation result
     */
    public function calculateSale(Model\SaleInterface $sale, string $currency = null, bool $cache = true): Model\Amount;

    /**
     * Calculates the sale's items amounts.
     *
     * @param Model\SaleInterface $sale
     * @param string              $currency The result currency
     * @param bool                $cache    Whether to use cached result
     *
     * @return Model\Amount
     *
     * @throws \Ekyna\Component\Commerce\Exception\LogicException If a root item is private.
     */
    public function calculateSaleItems(
        Model\SaleInterface $sale,
        string $currency = null,
        bool $cache = true
    ): Model\Amount;

    /**
     * Calculates the sale item amounts.
     *
     * @param Model\SaleItemInterface $item     The sale item
     * @param float                   $quantity The overridden quantity (optional)
     * @param string                  $currency The result currency
     * @param bool                    $cache    Whether to use cached result
     *
     * @return Model\Amount The sale item calculation result
     * @throws \Ekyna\Component\Commerce\Exception\LogicException
     *         If a private has discount adjustment or if a private item does not share the same tax group as its parent
     */
    public function calculateSaleItem(
        Model\SaleItemInterface $item,
        float $quantity = null,
        string $currency = null,
        bool $cache = true
    ): Model\Amount;

    /**
     * Calculates the sale discount result.
     *
     * @param Model\SaleAdjustmentInterface $adjustment The sale discount adjustment
     * @param Model\Amount                  $gross      The gross result (items sum)
     * @param Model\Amount                  $final      The final result to add the discount results to
     * @param string                        $currency   The result currency
     * @param bool                          $cache      Whether to use cached result
     *
     * @return Model\Amount The sale discount calculation result
     * @throws \Ekyna\Component\Commerce\Exception\LogicException
     */
    public function calculateSaleDiscount(
        Model\SaleAdjustmentInterface $adjustment,
        Model\Amount $gross,
        Model\Amount $final,
        string $currency = null,
        bool $cache = true
    ): Model\Amount;

    /**
     * Calculates the shipment result.
     *
     * @param Model\SaleInterface $sale     The sale
     * @param Model\Amount        $final    The final result to add the shipment result to.
     * @param string              $currency The result currency
     * @param bool                $cache    Whether to use cached result
     *
     * @return Model\Amount The sale shipment calculation result
     */
    public function calculateSaleShipment(
        Model\SaleInterface $sale,
        Model\Amount $final,
        string $currency = null,
        bool $cache = true
    ): ?Model\Amount;
}
