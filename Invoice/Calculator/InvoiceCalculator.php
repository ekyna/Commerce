<?php

namespace Ekyna\Component\Commerce\Invoice\Calculator;

use Ekyna\Component\Commerce\Common\Calculator\Result;
use Ekyna\Component\Commerce\Common\Model\AdjustmentModes;
use Ekyna\Component\Commerce\Common\Model\AdjustmentTypes;
use Ekyna\Component\Commerce\Common\Model\SaleItemInterface;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Exception\LogicException;
use Ekyna\Component\Commerce\Invoice\Model;

/**
 * Class InvoiceCalculator
 * @package Ekyna\Component\Commerce\Invoice\Calculator
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class InvoiceCalculator implements InvoiceCalculatorInterface
{
    /**
     * @var int
     */
    protected $precision = 2;


    /**
     * @inheritdoc
     */
    public function calculate(Model\InvoiceInterface $invoice)
    {
        if (null === $sale = $invoice->getSale()) {
            throw new LogicException("Invoice's sale must be set at this point.");
        }

        // TODO set precision from sale's currency

        $changed = false;

        $result = new Result();

        // Goods lines
        foreach ($invoice->getLinesByType(Model\InvoiceLineTypes::TYPE_GOOD) as $line) {
            $changed |= $this->calculateGoodLine($line, $result);
        }

        // Discount lines
        $goodsResult = clone $result;
        foreach ($invoice->getLinesByType(Model\InvoiceLineTypes::TYPE_DISCOUNT) as $line) {
            $changed |= $this->calculateDiscountLine($line, $goodsResult, $result);
        }

        // Invoice goods base (after discounts)
        if ($result->getBase() !== $invoice->getGoodsBase()) {
            $invoice->setGoodsBase($result->getBase());
            $changed = true;
        }

        // Shipment lines
        $shipmentBase = 0;
        foreach ($invoice->getLinesByType(Model\InvoiceLineTypes::TYPE_SHIPMENT) as $line) {
            $changed |= $this->calculateShipmentLine($line, $result);

            $shipmentBase += $line->getNetTotal();
        }

        // Invoice shipment base.
        if ($shipmentBase !== $invoice->getShipmentBase()) {
            $invoice->setShipmentBase($shipmentBase);
            $changed = true;
        }

        // Invoice taxes total
        $taxesTotal = $result->getTaxTotal();
        if ($taxesTotal !== $invoice->getTaxesTotal()) {
            $invoice->setTaxesTotal($taxesTotal);
            $changed = true;
        }

        // Taxes details
        $taxesDetails = [];
        foreach ($result->getTaxes() as $tax) {
            $taxesDetails[] = [
                'name'   => $tax->getName(),
                'rate'   => $tax->getRate(),
                'amount' => $tax->getAmount(),
            ];
        }
        if ($invoice->getTaxesDetails() !== $taxesDetails) {
            $invoice->setTaxesDetails($taxesDetails);
            $changed = true;
        }

        // Invoice grand total
        $grandTotal = $result->getTotal();
        if ($grandTotal !== $invoice->getGrandTotal()) {
            $invoice->setGrandTotal($grandTotal);
            $changed = true;
        }

        return $changed;
    }

    /**
     * Calculate the good line.
     *
     * @param Model\InvoiceLineInterface $line
     * @param Result                     $result
     *
     * @return bool
     * @throws LogicException
     */
    protected function calculateGoodLine(Model\InvoiceLineInterface $line, Result $result)
    {
        if ($line->getType() !== Model\InvoiceLineTypes::TYPE_GOOD) {
            throw new LogicException(sprintf(
                "Expected invoice line with type '%s'.",
                Model\InvoiceLineTypes::TYPE_GOOD
            ));
        }

        $changed = false;

        if (null === $item = $line->getSaleItem()) {
            throw new LogicException("Invoice can't be recalculated.");
        }

        $netUnit = $this->round($item->getNetPrice());
        if ($line->getNetPrice() != $netUnit) {
            $line->setNetPrice($netUnit);
            $changed = true;
        }

        $quantity = $line->getQuantity();
        $netTotal = $netUnit * $quantity;

        // Discounts
        $discountTotal = 0;
        if (!empty($adjustments = $this->getSaleItemDiscountAdjustments($item))) {
            foreach ($adjustments as $adjustment) {
                if ($adjustment->getMode() === AdjustmentModes::MODE_PERCENT) {
                    $discountTotal -= $this->round($netTotal * $adjustment->getAmount() / 100);
                } elseif ($adjustment->getMode() === AdjustmentModes::MODE_FLAT) {
                    $discountTotal -= $this->round($netTotal * $adjustment->getAmount() * ($quantity / $item->getTotalQuantity()));
                } else {
                    throw new InvalidArgumentException("Unexpected adjustment mode '{$adjustment->getMode()}'.");
                }
            }
            $netTotal += $discountTotal;
        }

        // Net total
        $result->addBase($netTotal);
        if ($netTotal !== $line->getNetTotal()) {
            $line->setNetTotal($netTotal);
            $changed = true;
        }

        // Discount total
        if ($discountTotal !== $line->getDiscountTotal()) {
            $line->setDiscountTotal($discountTotal);
            $changed = true;
        }

        // Taxes
        $taxRates = [];
        if (!empty($adjustments = $item->getAdjustments(AdjustmentTypes::TYPE_TAXATION)->toArray())) {
            /** @var \Ekyna\Component\Commerce\Common\Model\AdjustmentInterface $adjustment */
            foreach ($adjustments as $adjustment) {
                // Only percent type is allowed for taxation
                if ($adjustment->getMode() === AdjustmentModes::MODE_PERCENT) {
                    $result->addTax($adjustment->getDesignation(), $adjustment->getAmount(), $netTotal);
                    $taxRates[] = $adjustment->getAmount();
                } else {
                    throw new InvalidArgumentException("Unexpected adjustment mode '{$adjustment->getMode()}'.");
                }
            }
        }

        // Tax rates
        if ($taxRates !== $line->getTaxRates()) {
            $line->setTaxRates($taxRates);
            $changed = true;
        }

        return $changed;
    }

    /**
     * Calculate the discount line.
     *
     * @param Model\InvoiceLineInterface $line
     * @param Result                     $goodsResult
     * @param Result                     $result
     *
     * @return bool
     * @throws LogicException
     */
    protected function calculateDiscountLine(Model\InvoiceLineInterface $line, Result $goodsResult, Result $result)
    {
        if ($line->getType() !== Model\InvoiceLineTypes::TYPE_DISCOUNT) {
            throw new LogicException(sprintf(
                "Expected invoice line with type '%s'.",
                Model\InvoiceLineTypes::TYPE_DISCOUNT
            ));
        }

        $changed = false;

        if (null === $adjustment = $line->getSaleAdjustment()) {
            throw new LogicException("Invoice can't be recalculated.");
        }

        if ($adjustment->getMode() === AdjustmentModes::MODE_PERCENT) {
            $rate = $adjustment->getAmount() / 100;

            $discountAmount = -$this->round($goodsResult->getBase() * $rate);

            // Apply discount rate to base
            $result->addBase($discountAmount);

            // Apply discount rate to taxes
            foreach ($goodsResult->getTaxes() as $tax) {
                $taxBase = -$this->round($goodsResult->getBase() * $tax->getRate() / 100);
                $result->addTax($tax->getName(), $tax->getRate(), $taxBase);
            }
        } elseif ($adjustment->getMode() === AdjustmentModes::MODE_PERCENT) {
            $discountAmount = -$adjustment->getAmount();

            // Apply discount amount to base
            $result->addBase($discountAmount);

            // Dispatch the discount amount over taxes
            foreach ($goodsResult->getTaxes() as $tax) {
                $taxBase = -$this->round($adjustment->getAmount() * $tax->getBase() / $goodsResult->getBase());
                $result->addTax($tax->getName(), $tax->getRate(), $taxBase);
            }
        } else {
            throw new InvalidArgumentException("Unexpected adjustment mode '{$adjustment->getMode()}'.");
        }

        if ($discountAmount !== $line->getNetPrice()) {
            $line->setNetPrice($discountAmount);
            $changed = true;
        }
        if (0 !== $line->getDiscountTotal()) {
            $line->setDiscountTotal(0);
            $changed = true;
        }
        if ($discountAmount !== $line->getNetTotal()) {
            $line->setNetTotal($discountAmount);
            $changed = true;
        }

        return $changed;
    }

    /**
     * Calculate the shipment line.
     *
     * @param Model\InvoiceLineInterface $line
     * @param Result                     $result
     *
     * @return bool
     * @throws LogicException
     */
    protected function calculateShipmentLine(Model\InvoiceLineInterface $line, Result $result)
    {
        if ($line->getType() !== Model\InvoiceLineTypes::TYPE_SHIPMENT) {
            throw new LogicException(sprintf(
                "Expected invoice line with type '%s'.",
                Model\InvoiceLineTypes::TYPE_SHIPMENT
            ));
        }

        $changed = false;

        $sale = $line->getInvoice()->getSale();

        if (0 < $base = $sale->getShipmentAmount()) {
            $base = $this->round($base);

            $result->addBase($base);

            // Taxes
            if (!empty($adjustments = $sale->getAdjustments(AdjustmentTypes::TYPE_TAXATION)->toArray())) {
                /** @var \Ekyna\Component\Commerce\Common\Model\AdjustmentInterface $adjustment */
                foreach ($adjustments as $adjustment) {
                    // Only percent type is allowed for taxation
                    if ($adjustment->getMode() === AdjustmentModes::MODE_PERCENT) {
                        $result->addTax($adjustment->getDesignation(), $adjustment->getAmount(), $base);
                    } else {
                        throw new InvalidArgumentException("Unexpected adjustment mode '{$adjustment->getMode()}'.");
                    }
                }
            }
        }

        if ($base !== $line->getNetPrice()) {
            $line->setNetPrice($base);
            $changed = true;
        }
        if (0 !== $line->getDiscountTotal()) {
            $line->setDiscountTotal(0);
            $changed = true;
        }
        if ($base !== $line->getNetTotal()) {
            $line->setNetTotal($base);
            $changed = true;
        }

        return $changed;
    }

    /**
     * Returns the discount adjustments for the given sale item.
     *
     * @param SaleItemInterface $item
     *
     * @return \Ekyna\Component\Commerce\Common\Model\AdjustmentInterface[]
     */
    protected function getSaleItemDiscountAdjustments(SaleItemInterface $item)
    {
        $parent = $item;
        do {
            $adjustments = $parent->getAdjustments(AdjustmentTypes::TYPE_DISCOUNT)->toArray();
            if (!empty($adjustments)) {
                return $adjustments;
            }
            $parent = $parent->getParent();
        } while (null !== $parent);

        return [];
    }

    /**
     * Rounds the given amount.
     *
     * @param float $amount
     *
     * @return float
     */
    protected function round($amount)
    {
        return round($amount, $this->precision);
    }
}
