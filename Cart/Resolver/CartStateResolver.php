<?php

namespace Ekyna\Component\Commerce\Cart\Resolver;

use Ekyna\Component\Commerce\Cart\Model\CartInterface;
use Ekyna\Component\Commerce\Cart\Model\CartStates;
use Ekyna\Component\Commerce\Common\Resolver\AbstractSaleStateResolver;
use Ekyna\Component\Commerce\Common\Resolver\StateResolverInterface;
use Ekyna\Component\Commerce\Exception\UnexpectedTypeException;
use Ekyna\Component\Commerce\Payment\Model\PaymentStates;

/**
 * Class CartStateResolver
 * @package Ekyna\Component\Commerce\Cart\Resolver
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CartStateResolver extends AbstractSaleStateResolver implements StateResolverInterface
{
    /**
     * @inheritDoc
     *
     * @param CartInterface $subject
     */
    protected function resolveState(object $subject): string
    {
        if ($subject->hasItems()) {
            $paymentState = $subject->getPaymentState();

            // ACCEPTED If payment state is accepted or pending
            $acceptedStates = [
                PaymentStates::STATE_AUTHORIZED,
                PaymentStates::STATE_CAPTURED,
                PaymentStates::STATE_PAYEDOUT,
                PaymentStates::STATE_PENDING,
                PaymentStates::STATE_DEPOSIT,
                PaymentStates::STATE_COMPLETED,
            ];
            if (in_array($paymentState, $acceptedStates, true)) {
                return CartStates::STATE_ACCEPTED;
            }
        }

        return CartStates::STATE_NEW;
    }

    /**
     * @inheritDoc
     */
    protected function supports(object $subject): void
    {
        if (!$subject instanceof CartInterface) {
            throw new UnexpectedTypeException($subject, CartInterface::class);
        }
    }
}
