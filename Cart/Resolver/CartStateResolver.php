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

        parent::resolve($cart);

        if ($cart->hasItems()) {
            $paymentState = $cart->getPaymentState();

            $acceptedStates = [
                PaymentStates::STATE_CAPTURED,
                PaymentStates::STATE_AUTHORIZED,
                PaymentStates::STATE_PENDING,
            ];
            if (in_array($paymentState, $acceptedStates, true)) {
                return $this->setState($cart, CartStates::STATE_ACCEPTED);
            }
        }

        return $this->setState($cart, CartStates::STATE_NEW);
    }
}
