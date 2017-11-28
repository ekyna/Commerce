<?php

namespace Ekyna\Component\Commerce\Quote\Resolver;

use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Common\Resolver\AbstractSaleStateResolver;
use Ekyna\Component\Commerce\Common\Resolver\StateResolverInterface;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Payment\Model\PaymentStates;
use Ekyna\Component\Commerce\Quote\Model\QuoteInterface;
use Ekyna\Component\Commerce\Quote\Model\QuoteStates;

/**
 * Class QuoteStateResolver
 * @package Ekyna\Component\Commerce\Quote\Resolver
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class QuoteStateResolver extends AbstractSaleStateResolver implements StateResolverInterface
{
    /**
     * @inheritdoc
     */
    protected function resolveState(SaleInterface $sale)
    {
        if (!$sale instanceof QuoteInterface) {
            throw new InvalidArgumentException("Expected instance of " . QuoteInterface::class);
        }

        if ($sale->hasItems()) {
            $paymentState = $sale->getPaymentState();

            // ACCEPTED If payment state is accepted or pending
            $acceptedStates = [
                PaymentStates::STATE_CAPTURED,
                PaymentStates::STATE_AUTHORIZED,
                PaymentStates::STATE_PENDING,
                PaymentStates::STATE_COMPLETED,
            ];
            if (in_array($paymentState, $acceptedStates, true)) {
                return QuoteStates::STATE_ACCEPTED;
            }

            // REFUNDED If order has been refunded
            if (PaymentStates::STATE_REFUNDED === $paymentState) {
                return QuoteStates::STATE_REFUNDED;
            }

            // FAILED If all payments have failed
            if (PaymentStates::STATE_FAILED === $paymentState) {
                return QuoteStates::STATE_REFUSED;
            }

            // CANCELED If all payments have been canceled
            if (PaymentStates::STATE_CANCELED === $paymentState) {
                return QuoteStates::STATE_CANCELED;
            }
        }

        return QuoteStates::STATE_NEW;
    }
}
