<?php

namespace Ekyna\Component\Commerce\Cart\Resolver;

use Ekyna\Component\Commerce\Cart\Model\CartInterface;
use Ekyna\Component\Commerce\Cart\Model\CartStates;
use Ekyna\Component\Commerce\Common\Model\StateSubjectInterface;
use Ekyna\Component\Commerce\Common\Resolver\AbstractSaleStateResolver;
use Ekyna\Component\Commerce\Common\Resolver\StateResolverInterface;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Payment\Model\PaymentStates;

/**
 * Class CartStateResolver
 * @package Ekyna\Component\Commerce\Cart\Resolver
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CartStateResolver extends AbstractSaleStateResolver implements StateResolverInterface
{
    /**
     * @inheritdoc
     */
    public function resolve(StateSubjectInterface $cart)
    {
        if (!$cart instanceof CartInterface) {
            throw new InvalidArgumentException("Expected instance of CartInterface.");
        }

        $oldState = $cart->getState();
        $newState = CartStates::STATE_NEW;

        $paymentState = $this->resolvePaymentsState($cart);

        if ($cart->hasItems()) {
            if (in_array($paymentState, [PaymentStates::STATE_PENDING, PaymentStates::STATE_AUTHORIZED, PaymentStates::STATE_CAPTURED])) {
                $newState = CartStates::STATE_COMPLETED;
            } else {
                $newState = CartStates::STATE_NEW;
            }
        }

        $changed = false;

        if ($paymentState != $cart->getPaymentState()) {
            $cart->setPaymentState($paymentState);
            $changed = true;
        }

        if ($oldState != $newState) {
            $cart->setState($newState);
            $changed = true;
        }

        return $changed;
    }
}
