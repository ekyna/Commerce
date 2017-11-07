<?php

namespace Ekyna\Component\Commerce\Payment\Releaser;

use Ekyna\Component\Commerce\Common\Model\SaleInterface;
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
     * Constructor.
     *
     * @param PersistenceHelperInterface $persistenceHelper
     */
    public function __construct(PersistenceHelperInterface $persistenceHelper)
    {
        $this->persistenceHelper = $persistenceHelper;
    }

    /**
     * @inheritdoc
     */
    public function releaseFund(SaleInterface $sale)
    {
        $overpaidAmount = $sale->getPaidTotal() - $sale->getGrandTotal();

        // Abort if the sale is not overpaid
        if (0 >= $overpaidAmount) {
            return false;
        }

        $changed = false;

        // For each payments
        foreach ($sale->getPayments() as $payment) {
            // Continue if the payment does not use an outstanding balance method
            if (!$payment->getMethod()->isOutstanding()) {
                continue;
            }

            // Continue if the payment is not paid
            if (!PaymentStates::isPaidState($payment->getState())) {
                continue;
            }

            // If the payment amount is less than or equal the overpaid amount
            if ($payment->getAmount() <= $overpaidAmount) {
                // Cancel the payment
                $payment->setState(PaymentStates::STATE_CANCELED);

                $this->persistenceHelper->persistAndRecompute($payment, true);

                $changed = true;
                $overpaidAmount -= $payment->getAmount();

                // Break if the sale is no no longer overpaid
                if (0 >= $overpaidAmount) {
                    break;
                }
            }
        }

        return $changed;
    }
}
