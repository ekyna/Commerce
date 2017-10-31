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

        $changed = parent::resolve($quote);

        $paymentState = $quote->getPaymentState();

        $state = QuoteStates::STATE_NEW;

        if ($quote->hasItems()) {
            if (PaymentStates::isPaidState($paymentState)) {
                $state = QuoteStates::STATE_ACCEPTED;
            } elseif ($paymentState == PaymentStates::STATE_PENDING) {
                $state = QuoteStates::STATE_PENDING;
            } elseif ($paymentState == PaymentStates::STATE_FAILED) {
                $state = QuoteStates::STATE_REFUSED;
            } elseif ($paymentState == PaymentStates::STATE_REFUNDED) {
                $state = QuoteStates::STATE_REFUNDED;
            } elseif ($paymentState == PaymentStates::STATE_CANCELLED) {
                $state = QuoteStates::STATE_CANCELLED;
            } else {
                $state = QuoteStates::STATE_NEW;
            }
        }

        if ($state !== $quote->getState()) {
            $quote->setState($state);
            $changed = true;
        }

        return $changed;
    }
}
