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
    private readonly AmountCalculatorFactory           $calculatorFactory;
    private readonly InvoiceSubjectCalculatorInterface $invoiceCalculator;
    private readonly WeightCalculatorInterface         $weightCalculator;
    private readonly ShipmentPriceResolverInterface    $shipmentPriceResolver;
    private readonly ShipmentAddressResolverInterface  $shipmentAddressResolver;
    private readonly SubjectHelperInterface            $subjectHelper;
    private readonly CurrencyConverterInterface        $currencyConverter;
    private readonly PurchaseCostGuesserInterface      $purchaseCostGuesser;

    private ?AmountCalculatorInterface $amountCalculator = null;

    /** @var Margin[] */
    private array $cache = [];

    /**
     * @internal Use Calculator factory
     */
    public function __construct(
        private readonly string      $currency,
        private readonly bool        $profit,
        private readonly ?StatFilter $filter
    ) {
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

    public function calculateSaleItem(Item $item, bool $single = false): ?Margin
    {
        $key = spl_object_hash($item) . ($single ? '_single' : '');
        if ($margin = $this->get($key)) {
            return $margin;
        }

        $margin = new Margin($this->currency);
        $this->set($key, $margin);

        $this->addSaleItemPurchaseCost($margin, $item);

        $result = $this->getAmountCalculator()->calculateSaleItem($item, null, $single, !$single);
        $margin->addSellingPrice($result->getBase());

        if ($single) {
            return $margin;
        }

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
        $key = spl_object_hash($sale) . '_shipment';
        if ($margin = $this->get($key)) {
            return $margin;
        }

        $this->set($key, $margin = new Margin($this->currency));

        // Sample sale case
        if ($sale->isSample()) {
            return $margin;
        }

        $result = $this->getAmountCalculator()->calculateSaleShipment($sale);
        $margin->addSellingPrice($result->getBase());

        if (!$this->profit) {
            return $margin;
        }

        $base = $this->currencyConverter->getDefaultCurrency();

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

            // TODO Avg if partially shipped

            return $margin;
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

    protected function addSaleItemPurchaseCost(Margin $margin, Item $item): void
    {
        if ($item->isCompound()) {
            return;
        }

        if ($item instanceof StockAssignmentsInterface && $item->hasStockAssignments()) {
            $count = 0;
            $quantity = new Decimal(0);
            $sum = new Decimal(0);
            $total = new Decimal(0);
            $avg = false;

            foreach ($item->getStockAssignments() as $assignment) {
                $count++;
                $quantity += $qty = $assignment->getSoldQuantity();

                if (null === $cost = $this->getAssignmentCost($assignment)) {
                    $avg = true;
                    if (null === $cost = $this->getPurchaseCost($item)) {
                        continue;
                    }
                }

                $sum += $cost;
                $total += $cost->mul($qty);
            }

            if (!$this->profit && (($qty = $item->getTotalQuantity()) > $quantity)) {
                $total = $sum->div($count)->mul($qty);
                $avg = true;
            }

            $margin
                ->addPurchaseCost($total)
                ->setAverage($avg);

            return;
        }

        $sale = $item->getRootSale();
        if ($this->profit && $sale instanceof InvoiceSubjectInterface) {
            $quantity = $this->invoiceCalculator->calculateSoldQuantity($item);
        } else {
            $quantity = $item->getTotalQuantity();
        }

        if ($cost = $this->getPurchaseCost($item)) {
            $margin->addPurchaseCost($cost->mul($quantity));
        }

        $margin->setAverage(true);
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
     * Returns whether the given item should be skipped regarding the configured filter.
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
