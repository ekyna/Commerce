<?php

namespace Ekyna\Component\Commerce\Common\Updater;

use Ekyna\Component\Commerce\Common\Builder\AddressBuilderInterface;
use Ekyna\Component\Commerce\Common\Builder\AdjustmentBuilderInterface;
use Ekyna\Component\Commerce\Common\Calculator\AmountCalculatorInterface;
use Ekyna\Component\Commerce\Common\Calculator\WeightCalculatorInterface;
use Ekyna\Component\Commerce\Common\Factory\SaleFactoryInterface;
use Ekyna\Component\Commerce\Common\Model\AddressInterface;
use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Common\Util\Money;
use Ekyna\Component\Commerce\Invoice\Calculator\InvoiceCalculatorInterface;
use Ekyna\Component\Commerce\Invoice\Model\InvoiceSubjectInterface;
use Ekyna\Component\Commerce\Payment\Calculator\PaymentCalculatorInterface;
use Ekyna\Component\Commerce\Payment\Model\PaymentStates;
use Ekyna\Component\Commerce\Payment\Releaser\ReleaserInterface;

/**
 * Class SaleUpdater
 * @package Ekyna\Component\Commerce\Common\Updater
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SaleUpdater implements SaleUpdaterInterface
{
    /**
     * @var AddressBuilderInterface
     */
    protected $addressBuilder;

    /**
     * @var AdjustmentBuilderInterface
     */
    protected $adjustmentBuilder;

    /**
     * @var AmountCalculatorInterface
     */
    protected $amountCalculator;

    /**
     * @var WeightCalculatorInterface
     */
    protected $weightCalculator;

    /**
     * @var PaymentCalculatorInterface
     */
    protected $paymentCalculator;

    /**
     * @var InvoiceCalculatorInterface
     */
    protected $invoiceCalculator;

    /**
     * @var ReleaserInterface
     */
    protected $outstandingReleaser;

    /**
     * @var SaleFactoryInterface
     */
    protected $saleFactory;


    /**
     * Constructor.
     *
     * @param AddressBuilderInterface    $addressBuilder
     * @param AdjustmentBuilderInterface $adjustmentBuilder
     * @param AmountCalculatorInterface $amountCalculator
     * @param WeightCalculatorInterface  $weightCalculator
     * @param PaymentCalculatorInterface $paymentCalculator
     * @param InvoiceCalculatorInterface $invoiceCalculator
     * @param ReleaserInterface          $outstandingReleaser
     * @param SaleFactoryInterface       $saleFactory
     */
    public function __construct(
        AddressBuilderInterface $addressBuilder,
        AdjustmentBuilderInterface $adjustmentBuilder,
        AmountCalculatorInterface $amountCalculator,
        WeightCalculatorInterface $weightCalculator,
        PaymentCalculatorInterface $paymentCalculator,
        InvoiceCalculatorInterface $invoiceCalculator,
        ReleaserInterface $outstandingReleaser,
        SaleFactoryInterface $saleFactory
    ) {
        $this->addressBuilder = $addressBuilder;
        $this->adjustmentBuilder = $adjustmentBuilder;
        $this->amountCalculator = $amountCalculator;
        $this->weightCalculator = $weightCalculator;
        $this->paymentCalculator = $paymentCalculator;
        $this->invoiceCalculator = $invoiceCalculator;
        $this->outstandingReleaser = $outstandingReleaser;
        $this->saleFactory = $saleFactory;
    }

    /**
     * @inheritdoc
     */
    public function recalculate(SaleInterface $sale, $force = false)
    {
        // TODO Test
        // 1. discounts
        $changed = $this->updateDiscounts($sale);
        // 2. weight total
        $changed |= $this->updateWeightTotal($sale);
        // 3. shipment amount (based on 2.)
        //TODO$changed |= $this->updateTotalWeight($sale);
        // 4. taxation (items and shipment)
        $changed |= $this->updateTaxation($sale);
        // 5. net and grand totals
        $changed |= $this->updateAmountsTotal($sale);
        // 6. paid and outstanding totals
        $changed |= $this->updatePaymentTotal($sale);
        // 7. invoice total
        $changed |= $this->updateInvoiceTotal($sale);

        return $changed;
    }

    /**
     * @inheritdoc
     */
    public function updateTotals(SaleInterface $sale)
    {
        $changed = $this->updateWeightTotal($sale);

        $changed |= $this->updateAmountsTotal($sale);

        $changed |= $this->updatePaymentTotal($sale);

        $changed |= $this->updateInvoiceTotal($sale);

        return $changed;
    }

    /**
     * @inheritdoc
     */
    public function updateInvoiceAddressFromAddress(
        SaleInterface $sale,
        AddressInterface $source,
        $persistence = false
    ) {
        return $this->addressBuilder->buildSaleInvoiceAddressFromAddress($sale, $source, $persistence);
    }

    /**
     * @inheritdoc
     */
    public function updateDeliveryAddressFromAddress(
        SaleInterface $sale,
        AddressInterface $source,
        $persistence = false
    ) {
        return $this->addressBuilder->buildSaleDeliveryAddressFromAddress($sale, $source, $persistence);
    }

    /**
     * @inheritdoc
     */
    public function updateDiscounts(SaleInterface $sale, $persistence = false)
    {
        $changed = $this->adjustmentBuilder->buildDiscountAdjustmentsForSaleItems($sale, $persistence);

        return $this->adjustmentBuilder->buildDiscountAdjustmentsForSale($sale, $persistence) || $changed;
    }

    /**
     * @inheritdoc
     */
    public function updateTaxation(SaleInterface $sale, $persistence = false)
    {
        $changed = $this->adjustmentBuilder->buildTaxationAdjustmentsForSaleItems($sale, $persistence);

        return $this->adjustmentBuilder->buildTaxationAdjustmentsForSale($sale, $persistence) || $changed;
    }

    /**
     * @inheritdoc
     */
    public function updateShipmentTaxation(SaleInterface $sale, $persistence = false)
    {
        return $this->adjustmentBuilder->buildTaxationAdjustmentsForSale($sale, $persistence);
    }

    /**
     * @inheritdoc
     */
    public function updatePaymentTerm(SaleInterface $sale)
    {
        $term = null;

        if (null !== $customer = $sale->getCustomer()) {
            // From parent if available
            if ($customer->hasParent()) {
                $term = $customer->getParent()->getPaymentTerm();
            } else {
                $term = $customer->getPaymentTerm();
            }
        }

        if ($term !== $sale->getPaymentTerm()) {
            $sale->setPaymentTerm($term);

            return true;
        }

        return false;
    }

    /**
     * @inheritdoc
     */
    public function updateOutstandingDate(SaleInterface $sale)
    {
        $date = $this->resolveOutstandingDate($sale);

        if ($date !== $sale->getOutstandingDate()) {
            $sale->setOutstandingDate($date);

            return true;
        }

        return false;
    }

    /**
     * Updates the total weight.
     *
     * @param SaleInterface $sale
     *
     * @return bool Whether the sale has been changed or not.
     */
    protected function updateWeightTotal(SaleInterface $sale)
    {
        $weightTotal = $this->weightCalculator->calculateSale($sale);

        if ($sale->getWeightTotal() != $weightTotal) {
            $sale->setWeightTotal($weightTotal);

            return true;
        }

        return false;
    }

    /**
     * Updates the sale's net and grand totals.
     *
     * @param SaleInterface $sale
     *
     * @return bool Whether the sale has been changed or not.
     */
    protected function updateAmountsTotal(SaleInterface $sale)
    {
        $changed = false;

        $currency = $sale->getCurrency()->getCode();

        $sale->clearResults();

        $result = $this->amountCalculator->calculateSale($sale);

        if (0 != Money::compare($result->getBase(), $sale->getNetTotal(), $currency)) {
            $sale->setNetTotal($result->getBase());
            $changed = true;
        }

        if (0 != Money::compare($result->getTotal(), $sale->getGrandTotal(), $currency)) {
            $sale->setGrandTotal($result->getTotal());
            $changed = true;
        }

        return $changed;
    }

    /**
     * Updates the payment totals total.
     *
     * @param SaleInterface $sale
     *
     * @return bool Whether the sale has been changed or not.
     */
    protected function updatePaymentTotal(SaleInterface $sale)
    {
        $changed = false;

        $currency = $sale->getCurrency()->getCode();

        // Update paid total if needed
        $paid = $this->paymentCalculator->calculatePaidTotal($sale);
        if (0 != Money::compare($paid, $sale->getPaidTotal(), $currency)) {
            $sale->setPaidTotal($paid);
            $changed = true;
        }
        // Update accepted outstanding total if needed
        $acceptedOutstanding = $this->paymentCalculator->calculateOutstandingAcceptedTotal($sale);
        if (0 != Money::compare($acceptedOutstanding, $sale->getOutstandingAccepted(), $currency)) {
            $sale->setOutstandingAccepted($acceptedOutstanding);
            $changed = true;
        }
        // Update expired outstanding total if needed
        $expiredOutstanding = $this->paymentCalculator->calculateOutstandingExpiredTotal($sale);
        if (0 != Money::compare($expiredOutstanding, $sale->getOutstandingExpired(), $currency)) {
            $sale->setOutstandingExpired($expiredOutstanding);
            $changed = true;
        }

        // If payment totals has changed and fund has been released
        if ($changed && $this->outstandingReleaser->releaseFund($sale)) {
            // Re-update the payment totals
            $sale->setPaidTotal($this->paymentCalculator->calculatePaidTotal($sale));
            $sale->setOutstandingAccepted($this->paymentCalculator->calculateOutstandingAcceptedTotal($sale));
            $sale->setOutstandingExpired($this->paymentCalculator->calculateOutstandingExpiredTotal($sale));
        }

        return $changed;
    }

    /**
     * Updates the sale invoice total.
     *
     * @param SaleInterface $sale
     *
     * @return bool
     */
    protected function updateInvoiceTotal(SaleInterface $sale)
    {
        if (!$sale instanceof InvoiceSubjectInterface) {
            return false;
        }

        $total = $this->invoiceCalculator->calculateTotal($sale);
        if (0 != Money::compare($total, $sale->getInvoiceTotal(), $sale->getCurrency()->getCode())) {
            $sale->setInvoiceTotal($total);

            return true;
        }

        return false;
    }

    /**
     * Resolves the outstanding date.
     *
     * @param SaleInterface $sale
     *
     * @return \DateTime|null
     */
    protected function resolveOutstandingDate(SaleInterface $sale)
    {
        if (!$sale instanceof InvoiceSubjectInterface) {
            return null;
        }

        if (null === $term = $sale->getPaymentTerm()) {
            return null;
        }

        if (!$this->saleHasOutstandingPayments($sale)) {
            return null;
        }

        if (null === $invoicedAt = $sale->getInvoicedAt()) {
            return null;
        }

        // Calculate outstanding date
        $date = clone $invoicedAt;
        $date->setTime(23, 59, 59);
        $date->modify(sprintf('+%s days', $term->getDays()));
        if ($term->getEndOfMonth()) {
            $date->modify('last day of this month');
        }

        return $date;
    }

    /**
     * Returns whether the sale has (accepted/expired) outstanding payments.
     *
     * @param SaleInterface $sale
     *
     * @return bool
     */
    protected function saleHasOutstandingPayments(SaleInterface $sale)
    {
        $allowedStates = [
            PaymentStates::STATE_CAPTURED,
            PaymentStates::STATE_AUTHORIZED,
            PaymentStates::STATE_EXPIRED,
        ];
        foreach ($sale->getPayments() as $payment) {
            if ($payment->getMethod()->isOutstanding() && in_array($payment->getState(), $allowedStates, true)) {
                return true;
            }
        }

        return false;
    }
}
