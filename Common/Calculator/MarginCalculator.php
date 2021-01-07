<?php

namespace Ekyna\Component\Commerce\Common\Calculator;

use Ekyna\Component\Commerce\Common\Currency\CurrencyConverterInterface;
use Ekyna\Component\Commerce\Common\Model\AdjustmentTypes;
use Ekyna\Component\Commerce\Common\Model\Margin;
use Ekyna\Component\Commerce\Common\Model\SaleInterface as Sale;
use Ekyna\Component\Commerce\Common\Model\SaleItemInterface as Item;
use Ekyna\Component\Commerce\Invoice\Calculator\InvoiceSubjectCalculatorInterface;
use Ekyna\Component\Commerce\Invoice\Model\InvoiceSubjectInterface;
use Ekyna\Component\Commerce\Shipment\Calculator\WeightCalculatorInterface;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentSubjectInterface;
use Ekyna\Component\Commerce\Shipment\Resolver\ShipmentAddressResolverInterface;
use Ekyna\Component\Commerce\Shipment\Resolver\ShipmentPriceResolverInterface;
use Ekyna\Component\Commerce\Stat\Calculator\StatFilter;
use Ekyna\Component\Commerce\Stock\Model\StockAssignmentInterface;
use Ekyna\Component\Commerce\Stock\Model\StockAssignmentsInterface;
use Ekyna\Component\Commerce\Subject\Guesser\PurchaseCostGuesserInterface;
use Ekyna\Component\Commerce\Subject\SubjectHelperInterface;

/**
 * Class MarginCalculator
 * @package Ekyna\Component\Commerce\Common\Calculator
 * @author  Etienne Dauvergne <contact@ekyna.com>
 *
 * TODO Factory / cache
 */
class MarginCalculator implements MarginCalculatorInterface
{
    /**
     * @var string
     */
    protected $currency;

    /**
     * @var bool
     */
    protected $profit;

    /**
     * @var StatFilter
     */
    protected $filter;

    /**
     * @var AmountCalculatorFactory
     */
    protected $calculatorFactory;

    /**
     * @var InvoiceSubjectCalculatorInterface
     */
    protected $invoiceCalculator;

    /**
     * @var WeightCalculatorInterface
     */
    protected $weightCalculator;

    /**
     * @var ShipmentPriceResolverInterface
     */
    protected $shipmentPriceResolver;

    /**
     * @var ShipmentAddressResolverInterface
     */
    protected $shipmentAddressResolver;

    /**
     * @var SubjectHelperInterface
     */
    protected $subjectHelper;

    /**
     * @var CurrencyConverterInterface
     */
    protected $currencyConverter;

    /**
     * @var PurchaseCostGuesserInterface
     */
    protected $purchaseCostGuesser;

    /**
     * @var AmountCalculatorInterface
     */
    protected $amountCalculator;

    /**
     * @var Margin[]
     */
    private $cache;


    /**
     * Constructor.
     *
     * @param string          $currency
     * @param bool            $profit
     * @param StatFilter|null $filter
     *
     * @internal Use Calculator factory
     */
    public function __construct(string $currency, bool $profit, StatFilter $filter = null)
    {
        $this->currency = $currency;
        $this->profit = $profit;
        $this->filter = $filter;
        $this->cache = [];
    }

    /**
     * Sets the amount calculator factory.
     *
     * @param AmountCalculatorFactory $calculatorFactory
     */
    public function setCalculatorFactory(AmountCalculatorFactory $calculatorFactory): void
    {
        $this->calculatorFactory = $calculatorFactory;
    }

    /**
     * Sets the invoice calculator.
     *
     * @param InvoiceSubjectCalculatorInterface $invoiceCalculator
     */
    public function setInvoiceCalculator(InvoiceSubjectCalculatorInterface $invoiceCalculator): void
    {
        $this->invoiceCalculator = $invoiceCalculator;
    }

    /**
     * Sets the weight calculator.
     *
     * @param WeightCalculatorInterface $weightCalculator
     */
    public function setWeightCalculator(WeightCalculatorInterface $weightCalculator): void
    {
        $this->weightCalculator = $weightCalculator;
    }

    /**
     * Sets the shipment price resolver.
     *
     * @param ShipmentPriceResolverInterface $shipmentPriceResolver
     */
    public function setShipmentPriceResolver(ShipmentPriceResolverInterface $shipmentPriceResolver): void
    {
        $this->shipmentPriceResolver = $shipmentPriceResolver;
    }

    /**
     * Sets the shipment address resolver.
     *
     * @param ShipmentAddressResolverInterface $shipmentAddressResolver
     */
    public function setShipmentAddressResolver(ShipmentAddressResolverInterface $shipmentAddressResolver): void
    {
        $this->shipmentAddressResolver = $shipmentAddressResolver;
    }

    /**
     * Sets the subject helper.
     *
     * @param SubjectHelperInterface $subjectHelper
     */
    public function setSubjectHelper(SubjectHelperInterface $subjectHelper): void
    {
        $this->subjectHelper = $subjectHelper;
    }

    /**
     * Sets the currency converter.
     *
     * @param CurrencyConverterInterface $currencyConverter
     */
    public function setCurrencyConverter(CurrencyConverterInterface $currencyConverter): void
    {
        $this->currencyConverter = $currencyConverter;
    }

    /**
     * Sets the purchase cost guesser.
     *
     * @param PurchaseCostGuesserInterface $purchaseCostGuesser
     */
    public function setPurchaseCostGuesser(PurchaseCostGuesserInterface $purchaseCostGuesser): void
    {
        $this->purchaseCostGuesser = $purchaseCostGuesser;
    }

    /**
     * @inheritdoc
     */
    public function calculateSale(Sale $sale): ?Margin
    {
        $key = spl_object_hash($sale);
        if ($margin = $this->get($key)) {
            return $margin;
        }

        if (!$sale->hasItems() || $sale->isSample()) {
            return null;
        }

        $margin = new Margin($this->currency);
        $this->set($key, $margin);

        $this->getAmountCalculator()->calculateSale($sale);

        $cancel = true;
        foreach ($sale->getItems() as $item) {
            if ($this->isItemSkipped($item)) {
                continue;
            }

            if (null !== $result = $this->calculateSaleItem($item)) {
                $margin->merge($result);
                $cancel = false;
            }
        }

        if ($cancel) {
            return null;
        }

        foreach ($sale->getAdjustments(AdjustmentTypes::TYPE_DISCOUNT) as $adjustment) {
            $result = $this->getAmountCalculator()->calculateSaleDiscount($adjustment);
            $margin->addSellingPrice(-$result->getBase());
        }

        if ($result = $this->calculateSaleShipment($sale)) {
            $margin->merge($result);
        }

        return $margin;
    }

    /**
     * @inheritDoc
     */
    public function calculateSaleItem(Item $item): ?Margin
    {
        $key = spl_object_hash($item);
        if ($margin = $this->get($key)) {
            return $margin;
        }

        $margin = new Margin($this->currency);
        $this->set($key, $margin);

        if (!$item->isCompound()) {
            if ($item instanceof StockAssignmentsInterface && $item->hasStockAssignments()) {
                foreach ($item->getStockAssignments() as $assignment) {
                    if (null !== $cost = $this->getAssignmentCost($assignment)) {
                        $margin->addPurchaseCost($cost * $assignment->getSoldQuantity());

                        continue;
                    }

                    if (null !== $cost = $this->getPurchaseCost($item)) {
                        $margin->addPurchaseCost($cost * $assignment->getSoldQuantity());
                    }

                    $margin->setAverage(true);
                }
            } else {
                $sale = $item->getSale();
                if ($sale instanceof InvoiceSubjectInterface) {
                    $sold = $this->invoiceCalculator->calculateSoldQuantity($item);
                } else {
                    $sold = $item->getTotalQuantity();
                }

                if (null !== $cost = $this->getPurchaseCost($item)) {
                    $margin
                        ->addPurchaseCost($cost * $sold)
                        ->setAverage(true);
                }
            }
        }

        $result = $this->getAmountCalculator()->calculateSaleItem($item);
        $margin->addSellingPrice($result->getBase());

        foreach ($item->getChildren() as $child) {
            if ($this->isItemSkipped($item)) {
                continue;
            }

            if (null !== $result = $this->calculateSaleItem($child)) {
                if ($child->isPrivate()) {
                    if (1 === bccomp($cost = $result->getPurchaseCost(), 0, 5)) {
                        $margin->addPurchaseCost($cost);
                    }
                    if ($result->isAverage()) {
                        $margin->setAverage(true);
                    }
                } else {
                    $margin->merge($result);
                }
            }
        }

        return $margin;
    }

    /**
     * @inheritDoc
     */
    public function calculateSaleShipment(Sale $sale): ?Margin
    {
        if (!$this->profit) {
            return null;
        }

        $key = spl_object_hash($sale) . '_shipment';
        if ($margin = $this->get($key)) {
            return $margin;
        }

        if ($sale instanceof InvoiceSubjectInterface) {
            $sold = $this->invoiceCalculator->calculateSoldQuantity($sale);
        } else {
            $sold = 1;
        }

        $price = 0;
        if (0 < $sold) {
            $price = $this
                ->currencyConverter
                ->convertWithSubject($sale->getShipmentAmount(), $sale, $this->currency);
        }

        $base = $this->currencyConverter->getDefaultCurrency();

        $margin = new Margin($this->currency, 0., $price);
        $this->set($key, $margin);

        if ($sale instanceof ShipmentSubjectInterface && 0 < $sale->getShipments()->count()) {
            foreach ($sale->getShipments() as $shipment) {
                $deliveryAddress = $this
                    ->shipmentAddressResolver
                    ->resolveReceiverAddress($shipment, true);

                $country = $deliveryAddress->getCountry();
                $method = $shipment->getMethod();

                if ($country && $method) {
                    $weight = $this
                        ->weightCalculator
                        ->calculateShipment($shipment);

                    $price = $this
                        ->shipmentPriceResolver
                        ->getPriceByCountryAndMethodAndWeight($country, $method, $weight);

                    if (null !== $price) {
                        $margin->addPurchaseCost(
                            $this->currencyConverter->convert(
                                $price->getPrice(), $base, $this->currency, $shipment->getShippedAt()
                            )
                        );
                        continue;
                    }
                }

                $margin->setAverage(true);
            }

            return $margin;
            // TODO Avg if partially shipped
        }

        $country = $sale->getDeliveryCountry();
        $method = $sale->getShipmentMethod();
        $weight = $sale->getShipmentWeight() ?? $sale->getWeightTotal();

        if ($country && $method) {
            $price = $this
                ->shipmentPriceResolver
                ->getPriceByCountryAndMethodAndWeight($country, $method, $weight);

            if (null !== $price) {
                $margin->addPurchaseCost(
                    $this->currencyConverter->convert($price->getPrice(), $base, $this->currency)
                );
            } else {
                $margin->setAverage(true);
            }
        } else {
            $margin->setAverage(true);
        }

        return $margin;
    }

    /**
     * Returns the amount calculator.
     *
     * @return AmountCalculatorInterface
     */
    protected function getAmountCalculator(): AmountCalculatorInterface
    {
        if ($this->amountCalculator) {
            return $this->amountCalculator;
        }

        return $this->amountCalculator = $this->calculatorFactory->create($this->currency, $this->profit);
    }

    /**
     * Returns the stock assignment purchase cost.
     *
     * @param StockAssignmentInterface $assignment
     *
     * @return float|null
     */
    protected function getAssignmentCost(StockAssignmentInterface $assignment): ?float
    {
        $unit = $assignment->getStockUnit();

        if (!$unit->getSupplierOrderItem()) {
            return null;
        }

        $price = $unit->getNetPrice();
        if ($this->profit) {
            $price += $unit->getShippingPrice();
        }

        return $this
            ->currencyConverter
            ->convertWithSubject($price, $unit, $this->currency);
    }

    /**
     * Returns whether the given item should be skipped regarding to the configured filter.
     *
     * @param Item $item
     *
     * @return bool
     */
    protected function isItemSkipped(Item $item): bool
    {
        if (!$this->filter) {
            return false;
        }

        if (!$item->hasSubjectIdentity()) {
            return false;
        }

        return $this->filter->hasSubject($item->getSubjectIdentity()) xor !$this->filter->isExcludeSubjects();
    }

    /**
     * Returns the sale item purchase cost.
     *
     * @param Item $item
     *
     * @return float|null
     */
    protected function getPurchaseCost(Item $item): ?float
    {
        if (null === $subject = $this->subjectHelper->resolve($item)) {
            return null;
        }

        if (null !== $cost = $this->purchaseCostGuesser->guess($subject, $this->currency, $this->profit)) {
            return $cost;
        }

        return null;
    }

    /**
     * Returns the cached margin if any.
     *
     * @param string $key
     *
     * @return Margin|null
     */
    protected function get(string $key): ?Margin
    {
        if (isset($this->cache[$key])) {
            return $this->cache[$key];
        }

        return null;
    }

    /**
     * Sets the cached margin.
     *
     * @param string $key
     * @param Margin $amount
     */
    protected function set(string $key, Margin $amount): void
    {
        $this->cache[$key] = $amount;
    }
}
