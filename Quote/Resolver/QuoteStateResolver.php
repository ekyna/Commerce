<?php

namespace Ekyna\Component\Commerce\Quote\Resolver;

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
    public function resolve($quote)
    {
        if (!$quote instanceof QuoteInterface) {
            throw new InvalidArgumentException("Expected instance of QuoteInterface.");
        }

        parent::resolve($quote);

        if ($quote->hasItems()) {
            $paymentState = $quote->getPaymentState();

            $acceptedStates = [
                PaymentStates::STATE_CAPTURED,
                PaymentStates::STATE_AUTHORIZED,
                PaymentStates::STATE_PENDING,
            ];
            if (in_array($paymentState, $acceptedStates, true)) {
                return $this->setState($quote, QuoteStates::STATE_ACCEPTED);
            }

            if (PaymentStates::STATE_REFUNDED === $paymentState) {
                return $this->setState($quote, QuoteStates::STATE_REFUNDED);
            }

            if (PaymentStates::STATE_FAILED === $paymentState) {
                return $this->setState($quote, QuoteStates::STATE_REFUSED);
            }

            if (PaymentStates::STATE_CANCELED === $paymentState) {
                return $this->setState($quote, QuoteStates::STATE_CANCELED);
            }
        }

        return $this->setState($quote, QuoteStates::STATE_NEW);
    }
}
