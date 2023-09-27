<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Document\Calculator;

use Decimal\Decimal;
use Ekyna\Component\Commerce\Common\Calculator\AmountCalculatorFactory;
use Ekyna\Component\Commerce\Common\Calculator\AmountCalculatorInterface;
use Ekyna\Component\Commerce\Common\Currency\CurrencyConverterInterface;
use Ekyna\Component\Commerce\Common\Helper\ViewHelper;
use Ekyna\Component\Commerce\Common\Model as Common;
use Ekyna\Component\Commerce\Common\Util\FormatterFactory;
use Ekyna\Component\Commerce\Common\Util\Money;
use Ekyna\Component\Commerce\Document\Model;
use Ekyna\Component\Commerce\Document\Util\DocumentUtil;
use Ekyna\Component\Commerce\Exception\LogicException;

/**
 * Class DocumentCalculator
 * @package Ekyna\Component\Commerce\Document\Calculator
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class DocumentCalculator implements DocumentCalculatorInterface
{
    protected string                    $currency;
    protected bool                      $changed;
    protected AmountCalculatorInterface $calculator;
    protected ViewHelper                $viewHelper;

    public function __construct(
        protected readonly AmountCalculatorFactory    $calculatorFactory,
        protected readonly CurrencyConverterInterface $currencyConverter,
        protected readonly FormatterFactory           $formatterFactory,
    ) {
    }

    public function calculate(Model\DocumentInterface $document): bool
    {
        $this->changed = false;

        if (null === $document->getSale()) {
            throw new LogicException('Document can\'t be recalculated.');
        }

        $this->currency = $document->getCurrency();
        $this->calculator = $this->calculatorFactory->create($this->currency);
        $formatter = $this->formatterFactory->create($document->getLocale(), $this->currency);
        $this->viewHelper = new ViewHelper($formatter);

        $this->calculateDocument($document);

        if ($this->changed) {
            $quote = $this->currencyConverter->getDefaultCurrency();

            $rate = $this
                ->currencyConverter
                ->getSubjectExchangeRate($document->getSale(), $document->getCurrency(), $quote);

            $total = $this
                ->currencyConverter
                ->convertWithRate($document->getGrandTotal(), $rate, $quote);

            if (!$document->getRealGrandTotal()->equals($total)) {
                $document->setRealGrandTotal($total);
            }
        }

        return $this->changed;
    }

    /**
     * Calculates the document.
     *
     * @throws LogicException
     */
    protected function calculateDocument(Model\DocumentInterface $document): Result
    {
        // Goods lines / Gross result
        $gross = $this->calculateGoodLines($document);

        // Final result
        $final = Common\Amount::createFinalFromGross($gross);

        // Discount lines
        foreach ($document->getLinesByType(Model\DocumentLineTypes::TYPE_DISCOUNT) as $line) {
            $this->calculateDiscountLine($line, $gross, $final);
        }

        // Shipment lines
        $shipment = new Decimal(0);
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
     */
    protected function calculateGoodLines(Model\DocumentInterface $document): Common\Amount
    {
        $gross = new Common\Amount($this->currency);

        foreach ($document->getLinesByType(Model\DocumentLineTypes::TYPE_GOOD) as $line) {
            if ($result = $this->calculateGoodLine($line)) {
                $gross->merge($result);
            }
        }

        foreach ($document->getItems() as $item) {
            $gross->merge($this->calculateItem($item));
        }

        $gross->copyGrossToUnit();

        return $gross;
    }

    /**
     * Calculate the good line.
     *
     * @throws LogicException
     */
    protected function calculateGoodLine(Model\DocumentLineInterface $line): ?Common\Amount
    {
        if ($line->getType() !== Model\DocumentLineTypes::TYPE_GOOD) {
            throw new LogicException(
                sprintf(
                    'Expected document line with type \'%s\'.',
                    Model\DocumentLineTypes::TYPE_GOOD
                )
            );
        }

        if (null === $item = $line->getSaleItem()) {
            throw new LogicException('Document can\'t be recalculated.');
        }

        $hasPublicParent = DocumentUtil::hasPublicParent($line->getDocument(), $item);

        $result = $this
            ->calculator
            ->calculateSaleItem($item, $line->getQuantity(), $item->isPrivate() && !$hasPublicParent);

        $this->syncLine($line, $result);

        // Abort if document contains one of the public parents
        // TODO What if document shows private child(ren) ?
        if ($item->isPrivate() && $hasPublicParent) {
            return null;
        }

        return $result;
    }

    /**
     * Calculates the item.
     *
     * @param Model\DocumentItemInterface $item
     *
     * @return Common\Amount
     */
    protected function calculateItem(Model\DocumentItemInterface $item): Common\Amount
    {
        $base = $item->getGross();
        $discounts = [];
        foreach ($item->getDiscountRates() as $name => $rate) {
            $rate = new Decimal((string)$rate);
            $amount = Money::round($base->mul($rate)->div(100), $this->currency);
            $base -= $amount;
            $discounts[] = new Common\Adjustment($name, $amount, $rate);
        }

        $base = $item->getBase();
        $taxes = [];
        foreach ($item->getTaxRates() as $name => $rate) {
            $rate = new Decimal((string)$rate);
            $amount = Money::round($base->mul($rate->div(100)->add(1)), $this->currency);
            $amount -= Money::round($base, $this->currency);
            $taxes[] = new Common\Adjustment($name, $amount, $rate);
        }

        return new Common\Amount(
            $this->currency, $item->getUnit(), $item->getGross(),
            $item->getDiscount(), $item->getBase(), $item->getTax(),
            $item->getTotal(), $discounts, $taxes
        );
    }

    /**
     * Calculate the discount line.
     *
     * @throws LogicException
     */
    protected function calculateDiscountLine(
        Model\DocumentLineInterface $line,
        Common\Amount               $gross,
        Common\Amount               $final
    ): void {
        if ($line->getType() !== Model\DocumentLineTypes::TYPE_DISCOUNT) {
            throw new LogicException(
                sprintf(
                    "Expected document line with type '%s'.",
                    Model\DocumentLineTypes::TYPE_DISCOUNT
                )
            );
        }

        /** @var Common\SaleAdjustmentInterface $adjustment */
        if (null === $adjustment = $line->getSaleAdjustment()) {
            throw new LogicException("Document can't be recalculated.");
        }

        $result = $this->calculator->calculateSaleDiscount($adjustment, $gross, $final);

        $this->syncLine($line, $result);
    }

    /**
     * Calculate the shipment line.
     *
     * @throws LogicException
     */
    protected function calculateShipmentLine(Model\DocumentLineInterface $line, Common\Amount $final): Common\Amount
    {
        if ($line->getType() !== Model\DocumentLineTypes::TYPE_SHIPMENT) {
            throw new LogicException(
                sprintf(
                    "Expected document line with type '%s'.",
                    Model\DocumentLineTypes::TYPE_SHIPMENT
                )
            );
        }

        $sale = $line->getDocument()->getSale();

        $result = $this->calculator->calculateSaleShipment($sale, $final);

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
        $gross = $result->getGross();
        $final = $result->getFinal();

        // Document goods base (after discounts)
        if (!$document->getGoodsBase()->equals($final->getGross())) {
            $document->setGoodsBase($gross->getBase());
            $this->changed = true;
        }

        // Document discount base.
        if (!$document->getDiscountBase()->equals($final->getDiscount())) {
            $document->setDiscountBase($final->getDiscount());
            $this->changed = true;
        }

        // Document shipment base.
        if (!$document->getShipmentBase()->equals($result->getShipment())) {
            $document->setShipmentBase($result->getShipment());
            $this->changed = true;
        }

        // Document taxes total
        if (!$document->getTaxesTotal()->equals($final->getTax())) {
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

        // Includes
        $includes = [];
        foreach ($final->getIncludedAdjustments() as $include) {
            $includes[] = [
                'name'   => $include->getName(),
                'amount' => $include->getAmount(),
            ];
        }
        if ($document->getIncludedDetails() !== $includes) {
            $document->setIncludedDetails($includes);
            $this->changed = true;
        }

        // Document grand total
        if (!$document->getGrandTotal()->equals($final->getTotal())) {
            $document->setGrandTotal($final->getTotal());
            $this->changed = true;
        }
    }

    /**
     * Synchronizes the line amounts with the given result.
     */
    protected function syncLine(Model\DocumentLineInterface $line, Common\Amount $result): void
    {
        // Unit
        if (!$line->getUnit()->equals($result->getUnit())) {
            $line->setUnit($result->getUnit());
            $this->changed = true;
        }

        // Gross
        if (!$line->getGross()->equals($result->getGross())) {
            $line->setGross($result->getGross());
            $this->changed = true;
        }

        // Discount
        if (!$line->getDiscount()->equals($result->getDiscount())) {
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
        if (!$line->getBase()->equals($result->getBase())) {
            $line->setBase($result->getBase());
            $this->changed = true;
        }

        // Tax
        if (!$line->getTax()->equals($result->getTax())) {
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

        // Includes
        $includes = [];
        if (!empty($adjustments = $result->getIncludedAdjustments())) {
            foreach ($adjustments as $adjustment) {
                $includes[] = [
                    'name'   => $adjustment->getName(),
                    'amount' => $adjustment->getAmount(),
                ];
            }
        }
        if ($includes !== $line->getIncludedDetails()) {
            $line->setIncludedDetails($includes);
            $this->changed = true;
        }

        // Included
        $included = $this->viewHelper->buildIncludesDescription($result, $line->getQuantity());
        if ($included !== $line->getIncluded()) {
            $line->setIncluded($included);
            $this->changed = true;
        }

        // Total
        if (!$line->getTotal()->equals($result->getTotal())) {
            $line->setTotal($result->getTotal());
            $this->changed = true;
        }
    }
}
