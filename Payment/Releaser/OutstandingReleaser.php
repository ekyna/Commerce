<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Payment\Releaser;

use Decimal\Decimal;
use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Common\Util\Money;
use Ekyna\Component\Commerce\Invoice\Model\InvoiceSubjectInterface;
use Ekyna\Component\Commerce\Payment\Model\PaymentStates;
use Ekyna\Component\Commerce\Payment\Updater\PaymentUpdaterInterface;
use Ekyna\Component\Resource\Persistence\PersistenceHelperInterface;

/**
 * Class OutstandingReleaser
 * @package Ekyna\Component\Commerce\Payment\Releaser
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class OutstandingReleaser implements ReleaserInterface
{
    private PersistenceHelperInterface $persistenceHelper;
    private PaymentUpdaterInterface $paymentUpdater;
    private string $defaultCurrency;

    public function __construct(
        PersistenceHelperInterface $persistenceHelper,
        PaymentUpdaterInterface $paymentUpdater,
        string $defaultCurrency
    ) {
        $this->persistenceHelper = $persistenceHelper;
        $this->paymentUpdater = $paymentUpdater;
        $this->defaultCurrency = $defaultCurrency;
    }

    public function releaseFund(SaleInterface $sale): bool
    {
        $overpaidAmount =
            $sale->getPaidTotal()
            - $sale->getRefundedTotal()
            + $sale->getOutstandingExpired()
            + $sale->getOutstandingAccepted();

        if ($sale instanceof InvoiceSubjectInterface && $sale->isFullyInvoiced()) {
            $overpaidAmount -= $sale->getInvoiceTotal() - $sale->getCreditTotal();
        } else {
            $overpaidAmount -= $sale->getGrandTotal();
        }

        $overpaidAmount = Money::round($overpaidAmount, $this->defaultCurrency);

        // Abort if the sale is not overpaid
        if (0 >= $overpaidAmount) {
            return false;
        }

        $changed = false;

        $paidStates = PaymentStates::getPaidStates();
        $paidStates[] = PaymentStates::STATE_EXPIRED;

        // For each payment
        foreach ($sale->getPayments(true) as $payment) {
            // Continue if the payment does not use an outstanding balance method
            if (!$payment->getMethod()->isOutstanding()) {
                continue;
            }

            // Continue if the payment is not paid
            if (!in_array($payment->getState(), $paidStates, true)) {
                continue;
            }

            // If the payment amount is less than or equal the overpaid amount
            $amount = $payment->getRealAmount();
            if ($overpaidAmount >= $amount) {
                // Cancel the payment
                $payment->setState(PaymentStates::STATE_CANCELED);

                $this->persistenceHelper->persistAndRecompute($payment, true);
                $changed = true;

                $overpaidAmount = Money::round($overpaidAmount - $amount, $this->defaultCurrency);
            } else {
                $amount -= $overpaidAmount;

                if ($this->paymentUpdater->updateRealAmount($payment, $amount)) {
                    $this->persistenceHelper->persistAndRecompute($payment, true);

                    $changed = true;

                    $overpaidAmount = new Decimal(0);
                }
            }

            // Break if the sale is no longer overpaid
            if (0 >= $overpaidAmount) {
                break;
            }
        }

        return $changed;
    }
}
