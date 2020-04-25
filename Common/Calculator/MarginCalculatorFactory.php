<?php

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
    /**
     * @var AmountCalculatorFactory
     */
    protected $calculatorFactory;

    /**
     * @var InvoiceSubjectCalculatorInterface
     */
    protected $invoiceCalculator;

    /**
     * @var CurrencyConverterInterface
     */
    protected $currencyConverter;

    /**
     * @var SubjectHelperInterface
     */
    protected $subjectHelper;

    /**
     * @var PurchaseCostGuesserInterface
     */
    protected $purchaseCostGuesser;

    /**
     * @var ShipmentAddressResolverInterface
     */
    protected $shipmentAddressResolver;

    /**
     * @var WeightCalculatorInterface
     */
    protected $weightCalculator;

    /**
     * @var ShipmentPriceResolverInterface
     */
    protected $shipmentPriceResolver;


    /**
     * Constructor.
     *
     * @param AmountCalculatorFactory           $calculatorFactory
     * @param InvoiceSubjectCalculatorInterface $invoiceCalculator
     * @param CurrencyConverterInterface        $currencyConverter
     * @param SubjectHelperInterface            $subjectHelper
     * @param PurchaseCostGuesserInterface      $purchaseCostGuesser
     * @param ShipmentAddressResolverInterface  $shipmentAddressResolver
     * @param WeightCalculatorInterface         $weightCalculator
     * @param ShipmentPriceResolverInterface    $shipmentPriceResolver
     */
    public function __construct(
        AmountCalculatorFactory $calculatorFactory,
        InvoiceSubjectCalculatorInterface $invoiceCalculator,
        CurrencyConverterInterface $currencyConverter,
        SubjectHelperInterface $subjectHelper,
        PurchaseCostGuesserInterface $purchaseCostGuesser,
        ShipmentAddressResolverInterface $shipmentAddressResolver,
        WeightCalculatorInterface $weightCalculator,
        ShipmentPriceResolverInterface $shipmentPriceResolver
    ) {
        $this->calculatorFactory = $calculatorFactory;
        $this->invoiceCalculator = $invoiceCalculator;
        $this->currencyConverter = $currencyConverter;
        $this->subjectHelper = $subjectHelper;
        $this->purchaseCostGuesser = $purchaseCostGuesser;
        $this->shipmentAddressResolver = $shipmentAddressResolver;
        $this->weightCalculator = $weightCalculator;
        $this->shipmentPriceResolver = $shipmentPriceResolver;
    }

    /**
     * Returns a new margin calculator.
     *
     * @param string          $currency The currency
     * @param bool            $profit   Whether to calculate profit margin (taking transport in account)
     *                                  or commercial margin.
     * @param StatFilter|null $filter   The item filter
     *
     * @return MarginCalculatorInterface
     */
    public function create(
        string $currency = null,
        bool $profit = false,
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
