<?php

namespace Ekyna\Component\Commerce\Common\Calculator;

use Ekyna\Component\Commerce\Common\Currency\CurrencyConverterInterface;
use Ekyna\Component\Commerce\Common\Model;
use Ekyna\Component\Commerce\Shipment\Calculator\WeightCalculatorInterface;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentSubjectInterface;
use Ekyna\Component\Commerce\Shipment\Resolver\ShipmentAddressResolverInterface;
use Ekyna\Component\Commerce\Shipment\Resolver\ShipmentPriceResolverInterface;
use Ekyna\Component\Commerce\Stock\Model\StockAssignmentsInterface;
use Ekyna\Component\Commerce\Subject\Guesser\PurchaseCostGuesserInterface;
use Ekyna\Component\Commerce\Subject\SubjectHelperInterface;

/**
 * Class MarginCalculator
 * @package Ekyna\Component\Commerce\Common\Calculator
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class MarginCalculator implements MarginCalculatorInterface
{
    /**
     * @var AmountCalculatorInterface
     */
    private $amountCalculator;

    /**
     * @var WeightCalculatorInterface
     */
    private $weightCalculator;

    /**
     * @var ShipmentPriceResolverInterface
     */
    private $shipmentPriceResolver;

    /**
     * @var ShipmentAddressResolverInterface
     */
    private $shipmentAddressResolver;

    /**
     * @var SubjectHelperInterface
     */
    private $subjectHelper;

    /**
     * @var CurrencyConverterInterface
     */
    private $currencyConverter;

    /**
     * @var PurchaseCostGuesserInterface
     */
    private $purchaseCostGuesser;


    /**
     * Constructor.
     *
     * @param AmountCalculatorInterface        $amountCalculator
     * @param WeightCalculatorInterface        $weightCalculator
     * @param ShipmentPriceResolverInterface   $shipmentPriceResolver
     * @param ShipmentAddressResolverInterface $shipmentAddressResolver
     * @param SubjectHelperInterface           $subjectHelper
     * @param CurrencyConverterInterface       $currencyConverter
     * @param PurchaseCostGuesserInterface     $purchaseCostGuesser
     */
    public function __construct(
        AmountCalculatorInterface $amountCalculator,
        WeightCalculatorInterface $weightCalculator,
        ShipmentPriceResolverInterface $shipmentPriceResolver,
        ShipmentAddressResolverInterface $shipmentAddressResolver,
        SubjectHelperInterface $subjectHelper,
        CurrencyConverterInterface $currencyConverter,
        PurchaseCostGuesserInterface $purchaseCostGuesser
    ) {
        $this->amountCalculator = $amountCalculator;
        $this->weightCalculator = $weightCalculator;
        $this->shipmentPriceResolver = $shipmentPriceResolver;
        $this->shipmentAddressResolver = $shipmentAddressResolver;
        $this->subjectHelper = $subjectHelper;
        $this->currencyConverter = $currencyConverter;
        $this->purchaseCostGuesser = $purchaseCostGuesser;
    }

    /**
     * @inheritdoc
     */
    public function calculateSale(Model\SaleInterface $sale, string $currency = null): ?Model\Margin
    {
        $currency = $currency ?? $this->amountCalculator->getDefaultCurrency();

        if (null !== $margin = $sale->getMargin($currency)) {
            return $margin;
        }

        if (!$sale->hasItems() || $sale->isSample()) {
            return null;
        }

        $this->amountCalculator->calculateSale($sale, $currency);

        $margin = new Model\Margin($currency);

        $cancel = true;
        foreach ($sale->getItems() as $item) {
            if (null !== $itemMargin = $this->calculateSaleItem($item, $currency)) {
                $margin->merge($itemMargin);
                $cancel = false;
            }
        }

        if ($cancel) {
            return null;
        }

        foreach ($sale->getAdjustments(Model\AdjustmentTypes::TYPE_DISCOUNT) as $adjustment) {
            $margin->addSellingPrice(-$adjustment->getResult($currency)->getBase());
        }

        $margin->merge($this->calculateSaleShipment($sale, $currency));

        $sale->setMargin($margin);

        return $margin;
    }

    /**
     * @inheritdoc
     */
    public function calculateSaleItem(Model\SaleItemInterface $item, string $currency = null): ?Model\Margin
    {
        $currency = $currency ?? $this->amountCalculator->getDefaultCurrency();

        if (null !== $margin = $item->getMargin($currency)) {
            return $margin;
        }

        $margin = new Model\Margin($currency);

        $result = $this->amountCalculator->calculateSaleItem($item, null, $currency);

        $margin->addSellingPrice($result->getBase());

        if (!$item->isCompound()) {
            if ($item instanceof StockAssignmentsInterface && $item->hasStockAssignments()) {
                foreach ($item->getStockAssignments() as $assignment) {
                    if (0 < $netPrice = $assignment->getStockUnit()->getNetPrice()) {
                        $u = $assignment->getStockUnit();
                        $cost = $this
                            ->currencyConverter
                            ->convert($netPrice, $u->getCurrency(), $currency, $u->getExchangeDate());
                        $margin->addPurchaseCost($assignment->getSoldQuantity() * $cost);
                    } elseif (null !== $cost = $this->getPurchaseCost($item, $currency)) {
                        $margin
                            ->addPurchaseCost($cost * $assignment->getSoldQuantity())
                            ->setAverage(true);
                    } else {
                        $margin->setAverage(true);
                    }
                }
            } elseif (null !== $cost = $this->getPurchaseCost($item, $currency)) {
                $margin
                    ->addPurchaseCost($cost * $item->getTotalQuantity())
                    ->setAverage(true);
            } else {
                return null;
            }
        }

        foreach ($item->getChildren() as $child) {
            if (null !== $childMargin = $this->calculateSaleItem($child, $currency)) {
                if ($child->isPrivate()) {
                    if (0 < $cost = $childMargin->getPurchaseCost()) {
                        $margin->addPurchaseCost($childMargin->getPurchaseCost());
                    } else {
                        $margin->setAverage(true);
                    }
                } else {
                    $margin->merge($childMargin);
                }
            } else {
                $margin->setAverage(true);
            }
        }

        $item->setMargin($margin);

        return $margin;
    }

    /**
     * @inheritdoc
     */
    public function calculateSaleShipment(Model\SaleInterface $sale, string $currency = null): Model\Margin
    {
        $currency = $currency ?? $this->currencyConverter->getDefaultCurrency();
        $base = $sale->getCurrency()->getCode();

        if (!is_null($amount = $sale->getShipmentAmount())) {
            $rate = $this->currencyConverter->getSubjectExchangeRate($sale, $base, $currency);
            $sellPrice = $this->currencyConverter->convertWithRate($amount, $rate, $currency);
        } else {
            $sellPrice = 0;
        }

        $margin = new Model\Margin($currency, 0, $sellPrice);

        if ($sale instanceof ShipmentSubjectInterface && 0 < $sale->getShipments()->count()) {
            foreach ($sale->getShipments() as $shipment) {
                $deliveryAddress = $this->shipmentAddressResolver->resolveReceiverAddress($shipment, true);
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
                                $price->getPrice(), $base, $currency, $shipment->getShippedAt()
                            )
                        );
                        continue;
                    }
                }

                $margin->setAverage(true);
            }

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
                    $this->currencyConverter->convert($price->getPrice(), $base, $currency)
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
     * Returns the sale item purchase cost.
     *
     * @param Model\SaleItemInterface $item
     * @param string                  $currency
     *
     * @return float|null
     */
    private function getPurchaseCost(Model\SaleItemInterface $item, string $currency): ?float
    {
        /** @var \Ekyna\Component\Commerce\Subject\Model\SubjectInterface $subject */
        if (null === $subject = $this->subjectHelper->resolve($item)) {
            return null;
        }

        if (null !== $cost = $this->purchaseCostGuesser->guess($subject, $currency)) {
            return $cost;
        }

        return null;
    }
}
