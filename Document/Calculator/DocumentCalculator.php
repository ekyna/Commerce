<?php

namespace Ekyna\Component\Commerce\Document\Calculator;

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
    protected $synchronize;

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
    public function calculate(Model\DocumentInterface $document): bool
    {
        $this->changed = false;

        if (null === $sale = $document->getSale()) {
            throw new LogicException("Document can't be recalculated.");
        }

        $this->calculateDocument($document);

        if ($this->changed) {
            $converter = $this->calculator->getCurrencyConverter();
            $quote = $converter->getDefaultCurrency();
            $rate = $converter->getSubjectExchangeRate($document->getSale(), $document->getCurrency(), $quote);
            $total = $converter->convertWithRate($document->getGrandTotal(), $rate, $quote);

            if ($this->compareAmount($document->getRealGrandTotal(), $total)) {
                $document->setRealGrandTotal($total);
            }
        }

        return $this->changed;
    }

    /**
     * Calculates the document.
     *
     * @param Model\DocumentInterface $document
     * @param string                  $currency
     * @param bool                    $synchronize
     *
     * @return Result
     * @throws LogicException
     */
    protected function calculateDocument(
        Model\DocumentInterface $document,
        string $currency = null,
        bool $synchronize = true
    ): Result {
        $this->currency = $currency ?? $document->getCurrency();
        $this->synchronize = $synchronize;

        // Goods lines / Gross result
        $gross = $this->calculateGoodLines($document);

        // Final result
        $final = Common\Amount::createFinalFromGross($gross);

        // Discount lines
        foreach ($document->getLinesByType(Model\DocumentLineTypes::TYPE_DISCOUNT) as $line) {
            $this->calculateDiscountLine($line, $gross, $final);
        }

        // Shipment lines
        $shipment = 0;
        foreach ($document->getLinesByType(Model\DocumentLineTypes::TYPE_SHIPMENT) as $line) {
            $result = $this->calculateShipmentLine($line, $final);

            $shipment += $result->getBase();
        }

        // Round/finalize results.
        $gross->round();
        $final->finalize();

        $result = new Result($gross, $shipment, $final);

        $this->syncDocument($document, $result);

        return $result;
    }

    /**
     * Calculates the good lines.
     *
     * @param Model\DocumentInterface $document
     *
     * @return Common\Amount
     */
    protected function calculateGoodLines(Model\DocumentInterface $document): Common\Amount
    {
        $gross = new Common\Amount($this->currency);

        foreach ($document->getLinesByType(Model\DocumentLineTypes::TYPE_GOOD) as $line) {
            if (null !== $result = $this->calculateGoodLine($line)) {
                $gross->merge($result);
            }
        }

        $gross->copyGrossToUnit();

        return $gross;
    }

    /**
     * Calculate the good line.
     *
     * @param Model\DocumentLineInterface $line
     *
     * @return Common\Amount
     *
     * @throws LogicException
     */
    protected function calculateGoodLine(Model\DocumentLineInterface $line): ?Common\Amount
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

        $result = $this->calculator->calculateSaleItem($item, $line->getQuantity(), $this->currency, false);

        $this->syncLine($line, $result);

        if ($item->isPrivate()) {
            return null;
        }

        return $result;
    }

    /**
     * Calculate the discount line.
     *
     * @param Model\DocumentLineInterface $line
     * @param Common\Amount               $gross
     * @param Common\Amount               $final
     *
     * @throws LogicException
     */
    protected function calculateDiscountLine(
        Model\DocumentLineInterface $line,
        Common\Amount $gross,
        Common\Amount $final
    ): void {
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

        $result = $this->calculator->calculateSaleDiscount($adjustment, $gross, $final, $this->currency, false);

        $this->syncLine($line, $result);
    }

    /**
     * Calculate the shipment line.
     *
     * @param Model\DocumentLineInterface $line
     * @param Common\Amount               $final
     *
     * @return Common\Amount
     * @throws LogicException
     */
    protected function calculateShipmentLine(Model\DocumentLineInterface $line, Common\Amount $final): Common\Amount
    {
        if ($line->getType() !== Model\DocumentLineTypes::TYPE_SHIPMENT) {
            throw new LogicException(sprintf(
                "Expected document line with type '%s'.",
                Model\DocumentLineTypes::TYPE_SHIPMENT
            ));
        }

        $sale = $line->getDocument()->getSale();

        $result = $this->calculator->calculateSaleShipment($sale, $final, $this->currency, false);

        if (null === $result) {
            throw new LogicException("Unexpected document shipment line.");
        }

        $this->syncLine($line, $result);

        return $result;
    }

    /**
     * Synchronizes the document with the result.
     *
     * @param Model\DocumentInterface $document
     * @param Result                  $result
     */
    protected function syncDocument(Model\DocumentInterface $document, Result $result): void
    {
        if (!$this->synchronize) {
            return;
        }

        $gross = $result->getGross();
        $final = $result->getFinal();

        // Document goods base (after discounts)
        if ($this->compareAmount($document->getGoodsBase(), $final->getGross())) {
            $document->setGoodsBase($gross->getBase());
            $this->changed = true;
        }

        // Document discount base.
        if ($this->compareAmount($document->getDiscountBase(), $final->getDiscount())) {
            $document->setDiscountBase($final->getDiscount());
            $this->changed = true;
        }

        // Document shipment base.
        if ($this->compareAmount($document->getShipmentBase(), $result->getShipment())) {
            $document->setShipmentBase($result->getShipment());
            $this->changed = true;
        }

        // Document taxes total
        if ($this->compareAmount($document->getTaxesTotal(), $final->getTax())) {
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
        if ($this->compareAmount($document->getGrandTotal(), $final->getTotal())) {
            $document->setGrandTotal($final->getTotal());
            $this->changed = true;
        }
    }

    /**
     * Synchronizes the line amounts with the given result.
     *
     * @param Model\DocumentLineInterface $line
     * @param Common\Amount               $result
     */
    protected function syncLine(Model\DocumentLineInterface $line, Common\Amount $result): void
    {
        if (!$this->synchronize) {
            return;
        }

        // Unit
        if ($this->compareAmount($line->getUnit(), $result->getUnit())) {
            $line->setUnit($result->getUnit());
            $this->changed = true;
        }

        // Gross
        if ($this->compareAmount($line->getGross(), $result->getGross())) {
            $line->setGross($result->getGross());
            $this->changed = true;
        }

        // Discount
        if ($this->compareAmount($line->getDiscount(), $result->getDiscount())) {
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
        if ($this->compareAmount($line->getBase(), $result->getBase())) {
            $line->setBase($result->getBase());
            $this->changed = true;
        }

        // Tax
        if ($this->compareAmount($line->getTax(), $result->getTax())) {
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
        if ($this->compareAmount($line->getTotal(), $result->getTotal())) {
            $line->setTotal($result->getTotal());
            $this->changed = true;
        }
    }

    /**
     * Returns whether amount A is different than amount B.
     *
     * @param float $a
     * @param float $b
     *
     * @return bool
     */
    protected function compareAmount(float $a, float $b): bool
    {
        return 0 !== Money::compare($a, $b, $this->currency);
    }
}
