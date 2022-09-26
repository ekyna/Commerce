<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Common\Calculator;

use Ekyna\Component\Commerce\Common\Currency\CurrencyConverterInterface;
use Ekyna\Component\Commerce\Invoice\Calculator\InvoiceSubjectCalculatorInterface;
use Ekyna\Component\Commerce\Shipment\Calculator\WeightCalculatorInterface;
use Ekyna\Component\Commerce\Shipment\Resolver\ShipmentAddressResolverInterface;
use Ekyna\Component\Commerce\Shipment\Resolver\ShipmentPriceResolverInterface;
use Ekyna\Component\Commerce\Stat\Calculator\StatFilter;
use Ekyna\Component\Commerce\Subject\Guesser\PurchaseCostGuesserInterface;
use Ekyna\Component\Commerce\Subject\SubjectHelperInterface;

/**
 * Class MarginCalculatorFactory
 * @package Ekyna\Component\Commerce\Common\Calculator
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class MarginCalculatorFactory
{
    public function __construct(
        private readonly AmountCalculatorFactory           $calculatorFactory,
        private readonly InvoiceSubjectCalculatorInterface $invoiceCalculator,
        private readonly CurrencyConverterInterface        $currencyConverter,
        private readonly SubjectHelperInterface            $subjectHelper,
        private readonly PurchaseCostGuesserInterface      $purchaseCostGuesser,
        private readonly ShipmentAddressResolverInterface  $shipmentAddressResolver,
        private readonly WeightCalculatorInterface         $weightCalculator,
        private readonly ShipmentPriceResolverInterface    $shipmentPriceResolver
    ) {
    }

    /**
     * Returns a new margin calculator.
     *
     * @param string|null     $currency The currency
     * @param bool            $profit   Whether to calculate profit margin (taking transport in account)
     *                                  or commercial margin.
     * @param StatFilter|null $filter   The item filter
     */
    public function create(
        string     $currency = null,
        bool       $profit = false,
        StatFilter $filter = null
    ): MarginCalculatorInterface {
        $currency = $currency ?? $this->currencyConverter->getDefaultCurrency();

        $calculator = new MarginCalculator($currency, $profit, $filter);

        $calculator->setCalculatorFactory($this->calculatorFactory);
        $calculator->setInvoiceCalculator($this->invoiceCalculator);
        $calculator->setWeightCalculator($this->weightCalculator);
        $calculator->setShipmentPriceResolver($this->shipmentPriceResolver);
        $calculator->setShipmentAddressResolver($this->shipmentAddressResolver);
        $calculator->setSubjectHelper($this->subjectHelper);
        $calculator->setCurrencyConverter($this->currencyConverter);
        $calculator->setPurchaseCostGuesser($this->purchaseCostGuesser);

        return $calculator;
    }
}
