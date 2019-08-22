<?php

namespace Ekyna\Component\Commerce\Payment\Releaser;

use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Common\Util\Money;
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
     * @var PersistenceHelperInterface
     */
    private $persistenceHelper;

    /**
     * @var string
     */
    private $defaultCurrency;


    /**
     * Constructor.
     *
     * @param PersistenceHelperInterface $persistenceHelper
     * @param string                     $defaultCurrency
     */
    public function __construct(PersistenceHelperInterface $persistenceHelper, string $defaultCurrency)
    {
        $this->persistenceHelper = $persistenceHelper;
        $this->defaultCurrency = $defaultCurrency;
    }

    /**
     * @inheritdoc
     */
    public function releaseFund(SaleInterface $sale)
    {
        $overpaidAmount =
            $sale->getPaidTotal()
            + $sale->getOutstandingExpired()
            + $sale->getOutstandingAccepted()
            - Money::round($sale->getGrandTotal(), $this->defaultCurrency);

        $overpaidAmount = Money::round($overpaidAmount, $this->defaultCurrency);

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
            $amount = $payment->getRealAmount();
            // TODO convert payment amount using sale's exchange rate
            if (0 <= Money::compare($overpaidAmount, $amount, $this->defaultCurrency)) {
                // Cancel the payment
                $payment->setState(PaymentStates::STATE_CANCELED);

                $this->persistenceHelper->persistAndRecompute($payment, true);

                $changed = true;

                $overpaidAmount = Money::round($overpaidAmount - $amount, $this->defaultCurrency);

                // Break if the sale is no longer overpaid
                if (0 >= $overpaidAmount) {
                    break;
                }
            }
        }

        return $changed;
    }
}
