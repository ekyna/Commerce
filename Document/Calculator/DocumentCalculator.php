<?php

namespace Ekyna\Component\Commerce\Document\Calculator;

use Ekyna\Component\Commerce\Common\Calculator\Amount;
use Ekyna\Component\Commerce\Common\Calculator\AmountCalculatorInterface;
use Ekyna\Component\Commerce\Common\Model as Common;
use Ekyna\Component\Commerce\Common\Util\Money;
use Ekyna\Component\Commerce\Document\Model;
use Ekyna\Component\Commerce\Exception\LogicException;

/**
 * Class DocumentCalculator
 * @package Ekyna\Component\Commerce\Document\Calculator
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class DocumentCalculator implements DocumentCalculatorInterface
{
    /**
     * @var AmountCalculatorInterface
     */
    protected $calculator;

    /**
     * @var string
     */
    protected $currency;

    /**
     * @var bool
     */
    protected $changed;


    /**
     * Constructor.
     *
     * @param AmountCalculatorInterface $calculator
     */
    public function __construct(AmountCalculatorInterface $calculator)
    {
        $this->calculator = $calculator;
    }

    /**
     * @inheritdoc
     */
    public function calculate(Model\DocumentInterface $document)
    {
        $this->changed = false;
        $this->currency = $document->getCurrency();

        if (null === $sale = $document->getSale()) {
            throw new LogicException("Document can't be recalculated.");
        }

        // TODO Currency conversion
        $currency = $sale->getCurrency()->getCode();

        // Clear all sale's results and disable calculator cache
        $sale->clearResults();
        $this->calculator->setCache(false);

        // Goods lines / Gross result
        $gross = $this->calculateGoodLines($document);

        // Final result
        $final = Amount::createFinalFromGross($gross);

        // Discount lines
        foreach ($document->getLinesByType(Model\DocumentLineTypes::TYPE_DISCOUNT) as $line) {
            $this->calculateDiscountLine($line, $gross, $final);
        }

        // Shipment lines
        $shipmentBase = 0;
        foreach ($document->getLinesByType(Model\DocumentLineTypes::TYPE_SHIPMENT) as $line) {
            $result = $this->calculateShipmentLine($line, $final);

            $shipmentBase += $result->getBase();
        }

        // Round/finalize results.
        $gross->round($currency);
        $final->finalize($currency);

        // Document goods base (after discounts)
        if ($document->getGoodsBase() !== $gross->getBase()) {
            $document->setGoodsBase($gross->getBase());
            $this->changed = true;
        }

        // Document discount base.
        if ($document->getDiscountBase() !== $final->getDiscount()) {
            $document->setDiscountBase($final->getDiscount());
            $this->changed = true;
        }

        // Document shipment base.
        if ($document->getShipmentBase() !== $shipmentBase) {
            $document->setShipmentBase($shipmentBase);
            $this->changed = true;
        }

        // Document taxes total
        if ($document->getTaxesTotal() !== $final->getTax()) {
            $document->setTaxesTotal($final->getTax());
            $this->changed = true;
        }

        // Taxes details
        $taxesDetails = [];
        foreach ($final->getTaxAdjustments() as $tax) {
            $taxesDetails[] = [
                'name'   => $tax->getName(),
                'rate'   => $tax->getRate(),
                'amount' => $tax->getAmount(),
            ];
        }
        if ($document->getTaxesDetails() !== $taxesDetails) {
            $document->setTaxesDetails($taxesDetails);
            $this->changed = true;
        }

        // Document grand total
        if ($document->getGrandTotal() !== $final->getTotal()) {
            $document->setGrandTotal($final->getTotal());
            $this->changed = true;
        }

        // Clear all sale's results
        $sale->clearResults();

        return $this->changed;
    }

    /**
     * Calculates the good lines.
     *
     * @param Model\DocumentInterface $document
     *
     * @return Amount
     */
    protected function calculateGoodLines(Model\DocumentInterface $document): Amount
    {
        $gross = new Amount();

        foreach ($document->getLinesByType(Model\DocumentLineTypes::TYPE_GOOD) as $line) {
            $gross->merge($this->calculateGoodLine($line));
        }

        $gross->copyGrossToUnit();

        return $gross;
    }

    /**
     * Calculate the good line.
     *
     * @param Model\DocumentLineInterface $line
     *
     * @return Amount
     *
     * @throws LogicException
     */
    protected function calculateGoodLine(Model\DocumentLineInterface $line): Amount
    {
        if ($line->getType() !== Model\DocumentLineTypes::TYPE_GOOD) {
            throw new LogicException(sprintf(
                "Expected document line with type '%s'.",
                Model\DocumentLineTypes::TYPE_GOOD
            ));
        }

        if (null === $item = $line->getSaleItem()) {
            throw new LogicException("Document can't be recalculated.");
        }

        $result = $this->calculator->calculateSaleItem($item, $line->getQuantity());

        $this->syncLineWithResult($line, $result);

        return $result;
    }

    /**
     * Calculate the discount line.
     *
     * @param Model\DocumentLineInterface $line
     * @param Amount                      $gross
     * @param Amount                      $final
     *
     * @throws LogicException
     */
    protected function calculateDiscountLine(Model\DocumentLineInterface $line, Amount $gross, Amount $final)
    {
        if ($line->getType() !== Model\DocumentLineTypes::TYPE_DISCOUNT) {
            throw new LogicException(sprintf(
                "Expected document line with type '%s'.",
                Model\DocumentLineTypes::TYPE_DISCOUNT
            ));
        }

        /** @var Common\SaleAdjustmentInterface $adjustment */
        if (null === $adjustment = $line->getSaleAdjustment()) {
            throw new LogicException("Document can't be recalculated.");
        }

        $result = $this->calculator->calculateSaleDiscount($adjustment, $gross, $final);

        $this->syncLineWithResult($line, $result);
    }

    /**
     * Calculate the shipment line.
     *
     * @param Model\DocumentLineInterface $line
     * @param Amount                      $final
     *
     * @return Amount
     * @throws LogicException
     */
    protected function calculateShipmentLine(Model\DocumentLineInterface $line, Amount $final): Amount
    {
        if ($line->getType() !== Model\DocumentLineTypes::TYPE_SHIPMENT) {
            throw new LogicException(sprintf(
                "Expected document line with type '%s'.",
                Model\DocumentLineTypes::TYPE_SHIPMENT
            ));
        }

        $sale = $line->getDocument()->getSale();

        $result = $this->calculator->calculateSaleShipment($sale, $final);

        if (null === $result) {
            throw new LogicException("Unexpected document shipment line.");
        }

        $this->syncLineWithResult($line, $result);

        return $result;
    }

    /**
     * Synchronizes the line amounts with the given result.
     *
     * @param Model\DocumentLineInterface $line
     * @param Amount                      $result
     */
    protected function syncLineWithResult(Model\DocumentLineInterface $line, Amount $result)
    {
        // TODO Currency conversions

        // Unit
        if ($line->getUnit() !== $result->getUnit()) {
            $line->setUnit($result->getUnit());
            $this->changed = true;
        }
        // Gross
        if ($line->getGross() !== $result->getGross()) {
            $line->setGross($result->getGross());
            $this->changed = true;
        }
        // Discount
        if ($line->getDiscount() !== $result->getDiscount()) {
            $line->setDiscount($result->getDiscount());
            $this->changed = true;
        }
        // Discount rates
        $discountRates = [];
        if (!empty($adjustments = $result->getDiscountAdjustments())) {
            foreach ($adjustments as $adjustment) {
                $discountRates[] = $adjustment->getRate();
            }
        }
        if ($discountRates !== $line->getDiscountRates()) {
            $line->setDiscountRates($discountRates);
            $this->changed = true;
        }
        // Base
        if ($line->getBase() !== $result->getBase()) {
            $line->setBase($result->getBase());
            $this->changed = true;
        }
        // Tax
        if ($line->getTax() !== $result->getTax()) {
            $line->setTax($result->getTax());
            $this->changed = true;
        }
        // Tax rates
        $taxRates = [];
        if (!empty($adjustments = $result->getTaxAdjustments())) {
            foreach ($adjustments as $adjustment) {
                $taxRates[] = $adjustment->getRate();
            }
        }
        if ($taxRates !== $line->getTaxRates()) {
            $line->setTaxRates($taxRates);
            $this->changed = true;
        }
        // Total
        if ($line->getTotal() !== $result->getTotal()) {
            $line->setTotal($result->getTotal());
            $this->changed = true;
        }
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
