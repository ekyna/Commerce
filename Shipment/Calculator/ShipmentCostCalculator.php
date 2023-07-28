<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Shipment\Calculator;

use Ekyna\Component\Commerce\Common\Model\Cost;
use Ekyna\Component\Commerce\Common\Currency\CurrencyConverterInterface;
use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentInterface;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentSubjectInterface;
use Ekyna\Component\Commerce\Shipment\Resolver\ShipmentAddressResolverInterface;
use Ekyna\Component\Commerce\Shipment\Resolver\ShipmentPriceResolverInterface;
use Ekyna\Component\Commerce\Shipment\ShipmentUtil;

/**
 * Class ShipmentCostCalculator
 * @package Ekyna\Component\Commerce\Shipment\Calculator
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
class ShipmentCostCalculator implements ShipmentCostCalculatorInterface
{
    public function __construct(
        private readonly ShipmentAddressResolverInterface $shipmentAddressResolver,
        private readonly WeightCalculatorInterface        $shipmentWeightCalculator,
        private readonly ShipmentPriceResolverInterface   $shipmentPriceResolver,
        // TODO Remove (return results with default currency, and do conversion outside)
        private readonly CurrencyConverterInterface       $currencyConverter,
    ) {
    }

    public function calculateSale(SaleInterface $sale, string $currency): Cost
    {
        if ($sale instanceof ShipmentSubjectInterface && 0 < $sale->getShipments()->count()) {
            return $this->calculateSubject($sale, $currency);
        }

        $cost = new Cost();

        if (!$sale->hasPhysicalItem()) {
            return $cost;
        }

        $country = $sale->getDeliveryCountry();
        $method = $sale->getShipmentMethod();
        $weight = $sale->getShipmentWeight() ?? $sale->getWeightTotal();

        if (!($country && $method)) {
            $cost->setAverage();

            return $cost;
        }

        $price = $this
            ->shipmentPriceResolver
            ->getPriceByCountryAndMethodAndWeight($country, $method, $weight);

        if (null === $price) {
            $cost->setAverage();

            return $cost;
        }

        $base = $this->currencyConverter->getDefaultCurrency();

        $amount = $this->currencyConverter->convert(
            $price->getPrice(),
            $base,
            $currency
        );

        $cost->addShipment($amount);

        return $cost;
    }

    public function calculateSubject(ShipmentSubjectInterface $subject, string $currency): Cost
    {
        $cost = new Cost();

        foreach ($subject->getShipments() as $shipment) {
            $cost->add($this->calculateShipment($shipment, $currency));
        }

        return $cost;
    }

    public function calculateShipment(ShipmentInterface $shipment, string $currency): Cost
    {
        $cost = new Cost();

        if (!ShipmentUtil::hasPhysicalItem($shipment)) {
            return $cost;
        }

        $deliveryAddress = $this
            ->shipmentAddressResolver
            ->resolveReceiverAddress($shipment, true);

        $country = $deliveryAddress->getCountry();
        $method = $shipment->getMethod();

        if (!($country && $method)) {
            $cost->setAverage();

            return $cost;
        }

        // TODO Deal with parcels

        $weight = $this
            ->shipmentWeightCalculator
            ->calculateShipment($shipment);

        $price = $this
            ->shipmentPriceResolver
            ->getPriceByCountryAndMethodAndWeight($country, $method, $weight);

        if (null === $price) {
            $cost->setAverage();

            return $cost;
        }

        $base = $this->currencyConverter->getDefaultCurrency();

        $amount = $this->currencyConverter->convert(
            $price->getPrice(),
            $base,
            $currency,
            $shipment->getShippedAt()
        );

        $cost->addShipment($amount);

        return $cost;
    }
}
