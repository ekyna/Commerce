<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Common\Calculator;

use Decimal\Decimal;
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
    protected string                            $currency;
    protected bool                              $profit;
    protected ?StatFilter                       $filter;
    protected AmountCalculatorFactory           $calculatorFactory;
    protected InvoiceSubjectCalculatorInterface $invoiceCalculator;
    protected WeightCalculatorInterface         $weightCalculator;
    protected ShipmentPriceResolverInterface    $shipmentPriceResolver;
    protected ShipmentAddressResolverInterface  $shipmentAddressResolver;
    protected SubjectHelperInterface            $subjectHelper;
    protected CurrencyConverterInterface        $currencyConverter;
    protected PurchaseCostGuesserInterface      $purchaseCostGuesser;
    protected ?AmountCalculatorInterface        $amountCalculator = null;
    /** @var Margin[] */
    private array $cache;


    /**
     * @internal Use Calculator factory
     */
    public function __construct(string $currency, bool $profit, StatFilter $filter = null)
    {
        $this->currency = $currency;
        $this->profit = $profit;
        $this->filter = $filter;
        $this->cache = [];
    }

    public function setCalculatorFactory(AmountCalculatorFactory $calculatorFactory): void
    {
        $this->calculatorFactory = $calculatorFactory;
    }

    public function setInvoiceCalculator(InvoiceSubjectCalculatorInterface $invoiceCalculator): void
    {
        $this->invoiceCalculator = $invoiceCalculator;
    }

    public function setWeightCalculator(WeightCalculatorInterface $weightCalculator): void
    {
        $this->weightCalculator = $weightCalculator;
    }

    public function setShipmentPriceResolver(ShipmentPriceResolverInterface $shipmentPriceResolver): void
    {
        $this->shipmentPriceResolver = $shipmentPriceResolver;
    }

    public function setShipmentAddressResolver(ShipmentAddressResolverInterface $shipmentAddressResolver): void
    {
        $this->shipmentAddressResolver = $shipmentAddressResolver;
    }

    public function setSubjectHelper(SubjectHelperInterface $subjectHelper): void
    {
        $this->subjectHelper = $subjectHelper;
    }

    public function setCurrencyConverter(CurrencyConverterInterface $currencyConverter): void
    {
        $this->currencyConverter = $currencyConverter;
    }

    public function setPurchaseCostGuesser(PurchaseCostGuesserInterface $purchaseCostGuesser): void
    {
        $this->purchaseCostGuesser = $purchaseCostGuesser;
    }

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
            $margin->addSellingPrice($result->getBase()->negate());
        }

        if ($result = $this->calculateSaleShipment($sale)) {
            $margin->merge($result);
        }

        return $margin;
    }

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
                    if ($cost = $this->getAssignmentCost($assignment)) {
                        $margin->addPurchaseCost($cost->mul($assignment->getSoldQuantity()));

                        continue;
                    }

                    if ($cost = $this->getPurchaseCost($item)) {
                        $margin->addPurchaseCost($cost->mul($assignment->getSoldQuantity()));
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

                if ($cost = $this->getPurchaseCost($item)) {
                    $margin
                        ->addPurchaseCost($sold->mul($cost));
                }

                $margin->setAverage(true);
            }
        }

        $result = $this->getAmountCalculator()->calculateSaleItem($item);
        $margin->addSellingPrice($result->getBase());

        foreach ($item->getChildren() as $child) {
            if ($this->isItemSkipped($item)) {
                continue;
            }

            if ($result = $this->calculateSaleItem($child)) {
                if ($child->isPrivate()) {
                    if (0 < $cost = $result->getPurchaseCost()) {
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
            $sold = new Decimal(1);
        }

        $price = new Decimal(0);
        if (0 < $sold) {
            $price = $this
                ->currencyConverter
                ->convertWithSubject($sale->getShipmentAmount(), $sale, $this->currency);
        }

        $base = $this->currencyConverter->getDefaultCurrency();

        $margin = new Margin($this->currency, new Decimal(0), $price);
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

    protected function getAmountCalculator(): AmountCalculatorInterface
    {
        if ($this->amountCalculator) {
            return $this->amountCalculator;
        }

        return $this->amountCalculator = $this->calculatorFactory->create($this->currency, $this->profit);
    }

    /**
     * Returns the stock assignment purchase cost.
     */
    protected function getAssignmentCost(StockAssignmentInterface $assignment): ?Decimal
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
     */
    protected function getPurchaseCost(Item $item): ?Decimal
    {
        if (null === $subject = $this->subjectHelper->resolve($item)) {
            return null;
        }

        if ($cost = $this->purchaseCostGuesser->guess($subject, $this->currency, $this->profit)) {
            return $cost;
        }

        return null;
    }

    /**
     * Returns the cached margin if any.
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
     */
    protected function set(string $key, Margin $amount): void
    {
        $this->cache[$key] = $amount;
    }
}
