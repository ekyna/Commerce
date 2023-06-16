<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Invoice\Calculator;

use Ekyna\Component\Commerce\Common\Calculator\AmountCalculatorFactory;
use Ekyna\Component\Commerce\Common\Calculator\AmountCalculatorInterface;
use Ekyna\Component\Commerce\Common\Calculator\ItemCostCalculatorInterface;
use Ekyna\Component\Commerce\Common\Model\Margin;
use Ekyna\Component\Commerce\Common\Model\MarginCacheTrait;
use Ekyna\Component\Commerce\Document\Model\DocumentLineTypes;
use Ekyna\Component\Commerce\Exception\RuntimeException;
use Ekyna\Component\Commerce\Exception\UnexpectedTypeException;
use Ekyna\Component\Commerce\Invoice\Model\InvoiceInterface;
use Ekyna\Component\Commerce\Invoice\Model\InvoiceItemInterface;
use Ekyna\Component\Commerce\Invoice\Model\InvoiceLineInterface;
use Ekyna\Component\Commerce\Shipment\Calculator\ShipmentCostCalculatorInterface;

/**
 * Class InvoiceMarginCalculator
 * @package Ekyna\Component\Commerce\Invoice\Calculator
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class InvoiceMarginCalculator implements InvoiceMarginCalculatorInterface
{
    use MarginCacheTrait;

    private ?AmountCalculatorInterface $amountCalculator = null;

    /**
     * @param AmountCalculatorFactory         $calculatorFactory
     * @param ItemCostCalculatorInterface     $itemCostCalculator
     * @param ShipmentCostCalculatorInterface $shipmentCostCalculator
     * @param string                          $currency
     */
    public function __construct(
        private readonly AmountCalculatorFactory         $calculatorFactory,
        private readonly ItemCostCalculatorInterface     $itemCostCalculator,
        private readonly ShipmentCostCalculatorInterface $shipmentCostCalculator,
        private readonly string                          $currency
    ) {
    }

    public function calculateInvoice(InvoiceInterface $invoice): Margin
    {
        $key = 'invoice_' . spl_object_id($invoice);
        if ($margin = $this->get($key)) {
            return $margin;
        }

        $margin = new Margin();
        $this->set($key, $margin);

        foreach ($invoice->getLines() as $line) {
            $margin->merge($this->calculateInvoiceLine($line));
        }

        foreach ($invoice->getItems() as $item) {
            $margin->merge($this->calculateInvoiceItem($item));
        }

        return $margin;
    }

    public function calculateInvoiceLine(InvoiceLineInterface $line, bool $single = false): Margin
    {
        $margin = match ($line->getType()) {
            DocumentLineTypes::TYPE_GOOD     => $this->calculateGoodLine($line, $single),
            DocumentLineTypes::TYPE_DISCOUNT => $this->calculateDiscountLine($line),
            DocumentLineTypes::TYPE_SHIPMENT => $this->calculateShipmentLine($line),
            default                          => throw new UnexpectedTypeException($line, [
                DocumentLineTypes::TYPE_GOOD,
                DocumentLineTypes::TYPE_DISCOUNT,
                DocumentLineTypes::TYPE_SHIPMENT,
            ]),
        };

        // Negate credits
        if ($line->getInvoice()->isCredit()) {
            $margin->negate();
        }

        return $margin;
    }

    private function calculateGoodLine(InvoiceLineInterface $line, bool $single): Margin
    {
        $key = 'line_' . spl_object_id($line) . ($single ? '_single' : '');
        if ($margin = $this->get($key)) {
            return $margin;
        }

        if (null === $item = $line->getSaleItem()) {
            throw new RuntimeException('Sale item is not set.');
        }

        $margin = new Margin();
        $this->set($key, $margin);

        // Add item cost
        $cost = $this
            ->itemCostCalculator
            ->calculateSaleItem($item, $line->getQuantity(), $single);

        $margin->addCost($cost);

        // Add line revenue
        $amount = $this
            ->getAmountCalculator()
            ->calculateSaleItem($item, $line->getQuantity(), $single, !$single);

        $margin->addRevenueProduct($amount->getBase());

        return $margin;
    }

    private function calculateDiscountLine(InvoiceLineInterface $line): Margin
    {
        $key = 'discount_' . spl_object_id($line);
        if ($margin = $this->get($key)) {
            return $margin;
        }

        if (null === $line->getSaleAdjustment()) {
            throw new RuntimeException('Sale adjustment is not set.');
        }

        $margin = new Margin();
        $this->set($key, $margin);

        $margin->addRevenueProduct($line->getBase()->negate());

        return $margin;
    }

    private function calculateShipmentLine(InvoiceLineInterface $line): Margin
    {
        $key = 'shipment_' . spl_object_id($line);
        if ($margin = $this->get($key)) {
            return $margin;
        }

        if (null === $sale = $line->getSale()) {
            throw new RuntimeException('Sale is not set.');
        }

        $margin = new Margin();
        $this->set($key, $margin);

        // Add shipment revenue
        $margin->addRevenueShipment($line->getBase());

        // Add shipment cost
        $cost = $this
            ->shipmentCostCalculator
            ->calculateSale($sale, $this->currency);

        $margin->addCost($cost);

        return $margin;
    }

    public function calculateInvoiceItem(InvoiceItemInterface $item): Margin
    {
        $key = 'item_' . spl_object_id($item);
        if ($margin = $this->get($key)) {
            return $margin;
        }

        $margin = new Margin();
        $this->set($key, $margin);

        // Add item revenue
        $margin->addRevenueProduct($item->getBase());

        // Negate credits
        if ($item->getInvoice()->isCredit()) {
            $margin->negate();
        }

        return $margin;
    }

    private function getAmountCalculator(): AmountCalculatorInterface
    {
        if ($this->amountCalculator) {
            return $this->amountCalculator;
        }

        return $this->amountCalculator = $this->calculatorFactory->create($this->currency);
    }
}
