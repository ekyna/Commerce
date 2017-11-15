<?php

namespace Ekyna\Component\Commerce\Payment\Releaser;

use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Common\Util\Money;
use Ekyna\Component\Commerce\Payment\Calculator\PaymentCalculatorInterface;
use Ekyna\Component\Commerce\Payment\Model\PaymentStates;
use Ekyna\Component\Resource\Persistence\PersistenceHelperInterface;

/**
 * Class OutstandingReleaser
 * @package Ekyna\Component\Commerce\Payment\Releaser
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class OutstandingReleaser implements ReleaserInterface
{
    /**
     * @var PaymentCalculatorInterface
     */
    private $paymentCalculator;

    /**
     * @var PersistenceHelperInterface
     */
    private $persistenceHelper;


    /**
     * Constructor.
     *
     * @param PaymentCalculatorInterface $paymentCalculator
     * @param PersistenceHelperInterface $persistenceHelper
     */
    public function __construct(
        PaymentCalculatorInterface $paymentCalculator,
        PersistenceHelperInterface $persistenceHelper
    ) {
        $this->paymentCalculator = $paymentCalculator;
        $this->persistenceHelper = $persistenceHelper;
    }

    /**
     * @inheritdoc
     */
    public function releaseFund(SaleInterface $sale)
    {
        $currency = $sale->getCurrency()->getCode();

        $overpaidAmount =
            $sale->getPaidTotal()
            + $sale->getOutstandingExpired()
            + $sale->getOutstandingAccepted()
            - $sale->getGrandTotal();

        $overpaidAmount = Money::round($overpaidAmount, $currency);

        // Abort if the sale is not overpaid
        if (0 >= $overpaidAmount) {
            return false;
        }

        $changed = false;

        $paidStates = PaymentStates::getPaidStates();
        $paidStates[] = PaymentStates::STATE_EXPIRED;

        // For each payments
        foreach ($sale->getPayments() as $payment) {
            // Continue if the payment does not use an outstanding balance method
            if (!$payment->getMethod()->isOutstanding()) {
                continue;
            }

            // Continue if the payment is not paid
            if (!in_array($payment->getState(), $paidStates, true)) {
                continue;
            }

            // If the payment amount is less than or equal the overpaid amount
            $amount = $this->paymentCalculator->convertPaymentAmount($payment, $currency);
            if (0 <= Money::compare($overpaidAmount, $amount, $currency)) {
                // Cancel the payment
                $payment->setState(PaymentStates::STATE_CANCELED);

                $this->persistenceHelper->persistAndRecompute($payment, true);

                $changed = true;

                $overpaidAmount = Money::round($overpaidAmount - $amount, $currency);

                // Break if the sale is no no longer overpaid
                if (0 >= $overpaidAmount) {
                    break;
                }
            }
        }

        return $changed;
    }
}
