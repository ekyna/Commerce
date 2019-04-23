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
    public function calculateSale(Model\SaleInterface $sale): ?Margin
    {
        if (null !== $margin = $sale->getMargin()) {
            return $margin;
        }

        if (!$sale->hasItems() || $sale->isSample()) {
            return null;
        }

        $this->amountCalculator->calculateSale($sale);

        $margin = new Margin();

        $cancel = true;
        foreach ($sale->getItems() as $item) {
            if (null !== $itemMargin = $this->calculateSaleItem($item)) {
                $margin->merge($itemMargin);
                $cancel = false;
            }
        }

        if ($cancel) {
            return null;
        }

        foreach ($sale->getAdjustments(Model\AdjustmentTypes::TYPE_DISCOUNT) as $adjustment) {
            $margin->addSellingPrice(-$adjustment->getResult()->getBase());
        }

        $margin->merge($this->calculateSaleShipment($sale));

        $sale->setMargin($margin);

        return $margin;
    }

    /**
     * @inheritdoc
     */
    public function calculateSaleItem(Model\SaleItemInterface $item): ?Margin
    {
        if (null !== $margin = $item->getMargin()) {
            return $margin;
        }

        $margin = new Margin();

        $result = $this->amountCalculator->calculateSaleItem($item);
        $currency = $item->getSale()->getCurrency()->getCode();

        $margin->addSellingPrice($result->getBase());

        if (!$item->isCompound()) {
            if ($item instanceof StockAssignmentsInterface && $item->hasStockAssignments()) {
                foreach ($item->getStockAssignments() as $assignment) {
                    if (0 < $netPrice = $assignment->getStockUnit()->getNetPrice()) {
                        $c = $assignment->getStockUnit()->getCurrency();
                        $cost = $this->currencyConverter->convert($netPrice, $c, $currency);
                        $margin->addPurchaseCost($assignment->getSoldQuantity() * $cost);
                    } elseif (null !== $cost = $this->getPurchaseCost($item)) {
                        $margin
                            ->addPurchaseCost($cost * $assignment->getSoldQuantity())
                            ->setAverage(true);
                    } else {
                        $margin->setAverage(true);
                    }
                }
            } elseif (null !== $cost = $this->getPurchaseCost($item)) {
                $margin
                    ->addPurchaseCost($cost * $item->getTotalQuantity())
                    ->setAverage(true);
            } else {
                return null;
            }
        }

        foreach ($item->getChildren() as $child) {
            if (null !== $childMargin = $this->calculateSaleItem($child)) {
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
     * Returns the sale item purchase cost.
     *
     * @param Model\SaleItemInterface $item
     *
     * @return float|null
     */
    private function getPurchaseCost(Model\SaleItemInterface $item)
    {
        /** @var \Ekyna\Component\Commerce\Subject\Model\SubjectInterface $subject */
        if (null === $subject = $this->subjectHelper->resolve($item)) {
            return null;
        }

        $currency = $item->getSale()->getCurrency()->getCode();

        if (null !== $cost = $this->purchaseCostGuesser->guess($subject, $currency)) {
            return $cost;
        }

        return null;
    }

    /**
     * @inheritdoc
     */
    public function calculateSaleShipment(Model\SaleInterface $sale): Margin
    {
        $baseC = $this->currencyConverter->getDefaultCurrency();
        $quoteC = $sale->getCurrency()->getCode();

        if (($baseC != $quoteC) && (!is_null($rate = $sale->getExchangeRate()))) {
            $sellPrice = $this->currencyConverter->convertWithRate($sale->getShipmentAmount(), $rate, $quoteC);
        } else {
            $sellPrice = $this->currencyConverter->convert($sale->getShipmentAmount(), $baseC, $quoteC);
        }

        $margin = new Margin(0, $sellPrice);

        if ($sale instanceof ShipmentSubjectInterface && 0 < $sale->getShipments()->count()) {
            foreach ($sale->getShipments() as $shipment) {
                $deliveryAddress = $this->shipmentAddressResolver->resolveReceiverAddress($shipment, true);
                $country = $deliveryAddress->getCountry();
                $method = $shipment->getMethod();

                if ($country && $method) {
                    $weight = $this->weightCalculator->calculateShipment($shipment);
                    $price = $this
                        ->shipmentPriceResolver
                        ->getPriceByCountryAndMethodAndWeight($country, $method, $weight);

                    if (null !== $price) {
                        $margin->addPurchaseCost(
                            $this->currencyConverter->convert(
                                $price->getPrice(), $baseC, $quoteC, $shipment->getShippedAt()
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
        $weight = $sale->getWeightTotal();

        if ($country && $method) {
            $price = $this
                ->shipmentPriceResolver
                ->getPriceByCountryAndMethodAndWeight($country, $method, $weight);

            if (null !== $price) {
                $margin->addPurchaseCost(
                    $this->currencyConverter->convert($price->getPrice(), $baseC, $quoteC)
                );
            } else {
                $margin->setAverage(true);
            }
        } else {
            $margin->setAverage(true);
        }

        return $margin;
    }
}

