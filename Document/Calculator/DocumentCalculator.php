<?php

namespace Ekyna\Component\Commerce\Document\Calculator;

use Ekyna\Component\Commerce\Common\Calculator\Result;
use Ekyna\Component\Commerce\Common\Model as Common;
use Ekyna\Component\Commerce\Common\Util\Money;
use Ekyna\Component\Commerce\Document\Model;
use Ekyna\Component\Commerce\Exception\LogicException;
use InvalidArgumentException;

/**
 * Class DocumentCalculator
 * @package Ekyna\Component\Commerce\Document\Calculator
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class DocumentCalculator implements DocumentCalculatorInterface
{
    /**
     * @var string
     */
    protected $currency;


    /**
     * @inheritdoc
     */
    public function calculate(Model\DocumentInterface $document)
    {
        $this->currency = $document->getCurrency();

        $changed = false;

        $result = new Result();

        // Goods lines
        foreach ($document->getLinesByType(Model\DocumentLineTypes::TYPE_GOOD) as $line) {
            $changed |= $this->calculateGoodLine($line, $result);
        }

        // Discount lines
        $goodsResult = clone $result;
        foreach ($document->getLinesByType(Model\DocumentLineTypes::TYPE_DISCOUNT) as $line) {
            $changed |= $this->calculateDiscountLine($line, $goodsResult, $result);
        }

        // Document goods base (after discounts)
        if ($result->getBase() !== $document->getGoodsBase()) {
            $document->setGoodsBase($result->getBase());
            $changed = true;
        }

        // Shipment lines
        $shipmentBase = 0;
        foreach ($document->getLinesByType(Model\DocumentLineTypes::TYPE_SHIPMENT) as $line) {
            $changed |= $this->calculateShipmentLine($line, $result);

            $shipmentBase += $line->getNetTotal();
        }

        // Document shipment base.
        if ($shipmentBase !== $document->getShipmentBase()) {
            $document->setShipmentBase($shipmentBase);
            $changed = true;
        }

        // Document taxes total
        $taxesTotal = $result->getTaxTotal();
        if ($taxesTotal !== $document->getTaxesTotal()) {
            $document->setTaxesTotal($taxesTotal);
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
        if ($document->getTaxesDetails() !== $taxesDetails) {
            $document->setTaxesDetails($taxesDetails);
            $changed = true;
        }

        // Document grand total
        $grandTotal = $result->getTotal();
        if ($grandTotal !== $document->getGrandTotal()) {
            $document->setGrandTotal($grandTotal);
            $changed = true;
        }

        return $changed;
    }

    /**
     * Calculate the good line.
     *
     * @param Model\DocumentLineInterface $line
     * @param Result                     $result
     *
     * @return bool
     * @throws LogicException
     */
    protected function calculateGoodLine(Model\DocumentLineInterface $line, Result $result)
    {
        if ($line->getType() !== Model\DocumentLineTypes::TYPE_GOOD) {
            throw new LogicException(sprintf(
                "Expected document line with type '%s'.",
                Model\DocumentLineTypes::TYPE_GOOD
            ));
        }

        $changed = false;

        if (null === $item = $line->getSaleItem()) {
            throw new LogicException("Document can't be recalculated.");
        }

        $netUnit = $this->round($item->getNetPrice()); // TODO Currency conversion

        $quantity = $line->getQuantity();
        $netTotal = $baseTotal = $netUnit * $quantity;

        // Discounts
        $discountTotal = 0;
        if (!empty($adjustments = $this->getSaleItemDiscountAdjustments($item))) {
            foreach ($adjustments as $adjustment) {
                if ($adjustment->getMode() === Common\AdjustmentModes::MODE_PERCENT) {
                    $discountTotal -= $this->round($baseTotal * $adjustment->getAmount() / 100);
                } elseif ($adjustment->getMode() === Common\AdjustmentModes::MODE_FLAT) {
                    $discountTotal -= $this->round($baseTotal * $adjustment->getAmount() * ($quantity / $item->getTotalQuantity()));
                } else {
                    throw new InvalidArgumentException("Unexpected discount adjustment mode '{$adjustment->getMode()}'.");
                }
            }
            $netTotal += $discountTotal;
        }

        $result->addBase($netTotal);

        // Unit net price
        if ($line->getNetPrice() != $netUnit) {
            $line->setNetPrice($netUnit);
            $changed = true;
        }
        // Base total
        if ($baseTotal !== $line->getBaseTotal()) {
            $line->setBaseTotal($baseTotal);
            $changed = true;
        }
        // Discount total
        if ($discountTotal !== $line->getDiscountTotal()) {
            $line->setDiscountTotal($discountTotal);
            $changed = true;
        }
        // Net total
        if ($netTotal !== $line->getNetTotal()) {
            $line->setNetTotal($netTotal);
            $changed = true;
        }

        // Taxes
        $taxRates = [];
        if (!empty($adjustments = $item->getAdjustments(Common\AdjustmentTypes::TYPE_TAXATION)->toArray())) {
            /** @var Common\AdjustmentInterface $adjustment */
            foreach ($adjustments as $adjustment) {
                // Only percent type is allowed for taxation
                if ($adjustment->getMode() === Common\AdjustmentModes::MODE_PERCENT) {
                    $result->addTax($adjustment->getDesignation(), $adjustment->getAmount(), $netTotal);
                    $taxRates[] = $adjustment->getAmount();
                } else {
                    throw new InvalidArgumentException("Unexpected tax adjustment mode '{$adjustment->getMode()}'.");
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
     * @param Model\DocumentLineInterface $line
     * @param Result                     $goodsResult
     * @param Result                     $result
     *
     * @return bool
     * @throws LogicException
     */
    protected function calculateDiscountLine(Model\DocumentLineInterface $line, Result $goodsResult, Result $result)
    {
        if ($line->getType() !== Model\DocumentLineTypes::TYPE_DISCOUNT) {
            throw new LogicException(sprintf(
                "Expected document line with type '%s'.",
                Model\DocumentLineTypes::TYPE_DISCOUNT
            ));
        }

        $changed = false;

        if (null === $adjustment = $line->getSaleAdjustment()) {
            throw new LogicException("Document can't be recalculated.");
        }

        if ($adjustment->getMode() === Common\AdjustmentModes::MODE_PERCENT) {
            $rate = $adjustment->getAmount() / 100;

            $discountAmount = -$this->round($goodsResult->getBase() * $rate);

            // Apply discount rate to base
            $result->addBase($discountAmount);

            // Apply discount rate to taxes
            foreach ($goodsResult->getTaxes() as $tax) {
                $taxBase = -$this->round($tax->getBase() * $rate);
                $result->addTax($tax->getName(), $tax->getRate(), $taxBase);
            }
        } elseif ($adjustment->getMode() === Common\AdjustmentModes::MODE_FLAT) {
            $discountAmount = -$adjustment->getAmount(); // TODO Currency conversion

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

        // Unit net price
        if ($discountAmount !== $line->getNetPrice()) {
            $line->setNetPrice($discountAmount);
            $changed = true;
        }
        // Base total
        if ($discountAmount !== $line->getBaseTotal()) {
            $line->setBaseTotal($discountAmount);
            $changed = true;
        }
        // Discount total
        if (0 !== $line->getDiscountTotal()) {
            $line->setDiscountTotal(0);
            $changed = true;
        }
        // Net total
        if ($discountAmount !== $line->getNetTotal()) {
            $line->setNetTotal($discountAmount);
            $changed = true;
        }

        return $changed;
    }

    /**
     * Calculate the shipment line.
     *
     * @param Model\DocumentLineInterface $line
     * @param Result                     $result
     *
     * @return bool
     * @throws LogicException
     */
    protected function calculateShipmentLine(Model\DocumentLineInterface $line, Result $result)
    {
        if ($line->getType() !== Model\DocumentLineTypes::TYPE_SHIPMENT) {
            throw new LogicException(sprintf(
                "Expected document line with type '%s'.",
                Model\DocumentLineTypes::TYPE_SHIPMENT
            ));
        }

        $changed = false;

        $sale = $line->getDocument()->getSale();

        if (0 < $base = $sale->getShipmentAmount()) {
            $base = $this->round($base);

            $result->addBase($base);

            // Taxes
            if (!empty($adjustments = $sale->getAdjustments(Common\AdjustmentTypes::TYPE_TAXATION)->toArray())) {
                /** @var \Ekyna\Component\Commerce\Common\Model\AdjustmentInterface $adjustment */
                foreach ($adjustments as $adjustment) {
                    // Only percent type is allowed for taxation
                    if ($adjustment->getMode() === Common\AdjustmentModes::MODE_PERCENT) {
                        $result->addTax($adjustment->getDesignation(), $adjustment->getAmount(), $base);
                    } else {
                        throw new InvalidArgumentException("Unexpected adjustment mode '{$adjustment->getMode()}'.");
                    }
                }
            }
        }

        // Unit net price
        if ($base !== $line->getNetPrice()) { // TODO Currency conversion
            $line->setNetPrice($base);
            $changed = true;
        }
        // Base total
        if ($base !== $line->getBaseTotal()) {
            $line->setBaseTotal($base);
            $changed = true;
        }
        // Discount total
        if (0 !== $line->getDiscountTotal()) {
            $line->setDiscountTotal(0);
            $changed = true;
        }
        // Net total
        if ($base !== $line->getNetTotal()) {
            $line->setNetTotal($base);
            $changed = true;
        }

        return $changed;
    }

    /**
     * Returns the discount adjustments for the given sale item.
     *
     * @param Common\SaleItemInterface $item
     *
     * @return \Ekyna\Component\Commerce\Common\Model\AdjustmentInterface[]
     */
    protected function getSaleItemDiscountAdjustments(Common\SaleItemInterface $item)
    {
        $parent = $item;
        do {
            $adjustments = $parent->getAdjustments(Common\AdjustmentTypes::TYPE_DISCOUNT)->toArray();
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
        return Money::round($amount, $this->currency);
    }
}
