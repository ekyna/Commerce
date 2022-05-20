<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Invoice\Resolver;

use Decimal\Decimal;
use Ekyna\Component\Commerce\Common\Model\AdjustmentTypes;
use Ekyna\Component\Commerce\Common\Model\SaleAdjustmentInterface;
use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Common\Model\SaleItemInterface;
use Ekyna\Component\Commerce\Invoice\Calculator\InvoiceSubjectCalculatorInterface;
use Ekyna\Component\Commerce\Invoice\Model\InvoiceAvailability;
use Ekyna\Component\Commerce\Invoice\Model\InvoiceInterface;
use Ekyna\Component\Commerce\Shipment\Calculator\ShipmentSubjectCalculatorInterface;

use function max;
use function min;

/**
 * Class AvailabilityResolver
 * @package Ekyna\Component\Commerce\Invoice\Resolver
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class AvailabilityResolver
{
    private InvoiceSubjectCalculatorInterface  $invoiceCalculator;
    private ShipmentSubjectCalculatorInterface $shipmentCalculator;

    private ?InvoiceInterface $invoice;
    /** @var array<int, InvoiceAvailability> */
    private array $itemCache;
    /** @var array<int, InvoiceAvailability> */
    private array $adjustmentCache;
    /** @var array<int, InvoiceAvailability> */
    private array $shipmentCache;

    public function __construct(
        InvoiceSubjectCalculatorInterface  $invoiceCalculator,
        ShipmentSubjectCalculatorInterface $shipmentCalculator,
        ?InvoiceInterface                  $invoice
    ) {
        $this->invoiceCalculator = $invoiceCalculator;
        $this->shipmentCalculator = $shipmentCalculator;
        $this->invoice = $invoice;
        $this->itemCache = [];
        $this->adjustmentCache = [];
        $this->shipmentCache = [];
    }

    public function getInvoice(): ?InvoiceInterface
    {
        return $this->invoice;
    }

    public function resolveSaleItem(SaleItemInterface $item): InvoiceAvailability
    {
        if (isset($this->itemCache[$item->getId()])) {
            return $this->itemCache[$item->getId()];
        }

        $this->calculateSale($item->getRootSale());

        return $this->itemCache[$item->getId()];
    }

    public function resolveSaleDiscount(SaleAdjustmentInterface $adjustment): InvoiceAvailability
    {
        if (isset($this->adjustmentCache[$adjustment->getId()])) {
            return $this->adjustmentCache[$adjustment->getId()];
        }

        $this->calculateSale($adjustment->getSale());

        return $this->adjustmentCache[$adjustment->getId()];
    }

    public function resolveSaleShipment(SaleInterface $sale): InvoiceAvailability
    {
        if (isset($this->shipmentCache[$sale->getId()])) {
            return $this->shipmentCache[$sale->getId()];
        }

        $this->calculateSale($sale);

        return $this->shipmentCache[$sale->getId()];
    }

    private function calculateSale(SaleInterface $sale): void
    {
        foreach ($sale->getItems() as $item) {
            $this->calculateSaleItem($item);
        }

        foreach ($sale->getAdjustments(AdjustmentTypes::TYPE_DISCOUNT) as $adjustment) {
            $this->calculateSaleDiscount($adjustment);
        }

        $this->calculateSaleShipment($sale);
    }

    private function calculateSaleItem(SaleItemInterface $item): InvoiceAvailability
    {
        if ($item->isCompound()) {
            $expected = new Decimal(INF);
            $maximum = new Decimal(INF);
        } else {
            $isCredit = $this->invoice && $this->invoice->isCredit();

            if ($isCredit) {
                // Credit case
                $expected = new Decimal(0); // TODO
                $maximum = $this
                    ->invoiceCalculator
                    ->calculateCreditableQuantity($item, $this->invoice);
            } else {
                // Invoice case
                $maximum = $this
                    ->invoiceCalculator
                    ->calculateInvoiceableQuantity($item, $this->invoice);

                $temp = $this->shipmentCalculator->calculateShippedQuantity($item)
                    - $this->invoiceCalculator->calculateInvoicedQuantity($item, $this->invoice);

                $expected = max(new Decimal(0), min(
                    $maximum,
                    $temp
                ));
            }
        }

        $availability = new InvoiceAvailability($item, $expected, $maximum);

        foreach ($item->getChildren() as $child) {
            $childAvailability = $this->calculateSaleItem($child);

            $availability->addChild($childAvailability);

            if (!$child->isPrivate()) {
                continue;
            }

            $e = $childAvailability->getExpected()->div($child->getQuantity());
            if ($expected > $e) {
                $expected = $e;
            }

            $a = $childAvailability->getMaximum()->div($child->getQuantity());
            if ($maximum > $a) {
                $maximum = $a;
            }
        }

        $spread = $item->hasPrivateChildren();

        $availability
            ->setExpected($expected, $spread)
            ->setMaximum($maximum, $spread);

        return $this->itemCache[$item->getId()] = $availability;
    }

    private function calculateSaleDiscount(SaleAdjustmentInterface $adjustment): void
    {
        $expected = new Decimal(0);
        if ($this->invoice && $this->invoice->isCredit()) {
            // Credit case
            $maximum = $this
                ->invoiceCalculator
                ->calculateCreditableQuantity($adjustment, $this->invoice);
        } else {
            // Invoice case
            $expected = $maximum = $this
                ->invoiceCalculator
                ->calculateInvoiceableQuantity($adjustment, $this->invoice);
        }

        $this->adjustmentCache[$adjustment->getId()] = new InvoiceAvailability(null, $expected, $maximum);
    }

    private function calculateSaleShipment(SaleInterface $sale): void
    {
        $expected = new Decimal(0);
        if ($this->invoice && $this->invoice->isCredit()) {
            // Credit case
            $maximum = $this->invoiceCalculator->calculateCreditableQuantity($sale, $this->invoice);
        } else {
            // Invoice case
            $expected = $maximum = $this->invoiceCalculator->calculateInvoiceableQuantity($sale, $this->invoice);
        }

        $this->shipmentCache[$sale->getId()] = new InvoiceAvailability(null, $expected, $maximum);
    }
}
