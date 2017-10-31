<?php

namespace Ekyna\Component\Commerce\Cart\Resolver;

use Ekyna\Component\Commerce\Cart\Model\CartInterface;
use Ekyna\Component\Commerce\Cart\Model\CartStates;
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
    public function resolve($cart)
    {
        if (!$cart instanceof CartInterface) {
            throw new InvalidArgumentException("Expected instance of " . CartInterface::class);
        }

        $changed = parent::resolve($cart);

        $paymentState = $cart->getPaymentState();

        $state = CartStates::STATE_NEW;

        if ($cart->hasItems()) {
            if (
                PaymentStates::isPaidState($paymentState) ||
                $paymentState === PaymentStates::STATE_PENDING
            ) {
                $state = CartStates::STATE_ACCEPTED;
            }
        }

        if ($state !== $cart->getState()) {
            $cart->setState($state);
            $changed = true;
        }

        return $changed;
    }
}
