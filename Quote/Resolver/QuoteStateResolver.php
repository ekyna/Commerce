<?php

namespace Ekyna\Component\Commerce\Quote\Resolver;

use Ekyna\Component\Commerce\Common\Model\StateSubjectInterface;
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
    public function resolve(StateSubjectInterface $quote)
    {
        if (!$quote instanceof QuoteInterface) {
            throw new InvalidArgumentException("Expected instance of QuoteInterface.");
        }

        $oldState = $quote->getState();
        $newState = QuoteStates::STATE_NEW;

        $paymentState = $this->resolvePaymentsState($quote);

        $outstanding = $this->resolveOutstanding($quote, $paymentState);

        if ($quote->hasItems()) {
            if (PaymentStates::isPaidState($paymentState) || ($outstanding && $outstanding->isValid())) {
                $newState = QuoteStates::STATE_ACCEPTED;
            } elseif ($outstanding && !$outstanding->isExpired()) {
                $newState = QuoteStates::STATE_PENDING;
            } elseif ($paymentState == PaymentStates::STATE_PENDING) {
                $newState = QuoteStates::STATE_PENDING;
            } elseif ($paymentState == PaymentStates::STATE_FAILED) {
                $newState = QuoteStates::STATE_REFUSED;
            } elseif ($paymentState == PaymentStates::STATE_REFUNDED) {
                $newState = QuoteStates::STATE_REFUNDED;
            } elseif ($paymentState == PaymentStates::STATE_CANCELLED) {
                $newState = QuoteStates::STATE_CANCELLED;
            } else {
                $newState = QuoteStates::STATE_NEW;
            }
        }

        $changed = false;

        if ($paymentState != $quote->getPaymentState()) {
            $quote->setPaymentState($paymentState);
            $changed = true;
        }

        if ($oldState != $newState) {
            $quote->setState($newState);
            $changed = true;
        }

        return $changed;
    }
}
