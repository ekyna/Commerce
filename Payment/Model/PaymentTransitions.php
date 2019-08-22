<?php

namespace Ekyna\Component\Commerce\Payment\Model;

/**
 * Class PaymentTransitions
 * @package Ekyna\Component\Commerce\Payment\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class PaymentTransitions
{
    const TRANSITION_CANCEL = 'cancel';
    const TRANSITION_HANG   = 'hang';
    const TRANSITION_ACCEPT = 'accept';
    const TRANSITION_REJECT = 'reject';
    const TRANSITION_REFUND = 'refund';


    /**
     * Returns the available payment transitions.
     *
     * @param PaymentInterface $payment
     * @param bool             $admin
     *
     * @return array
     */
    static function getAvailableTransitions(PaymentInterface $payment, $admin = false)
    {
        // TODO use payum to select gateway's supported requests/actions.

        $transitions = [];

        $method = $payment->getMethod();
        $state = $payment->getState();

        if ($admin) {
            if ($method->isManual()) {
                switch ($state) {
                    case PaymentStates::STATE_PENDING:
                        $transitions[] = static::TRANSITION_CANCEL;
                        $transitions[] = static::TRANSITION_ACCEPT;
                        break;
                    case PaymentStates::STATE_CAPTURED:
                        $transitions[] = static::TRANSITION_CANCEL;
                        $transitions[] = static::TRANSITION_HANG;
                        $transitions[] = static::TRANSITION_REFUND;
                        break;
                    case PaymentStates::STATE_REFUNDED:
                        $transitions[] = static::TRANSITION_CANCEL;
                        $transitions[] = static::TRANSITION_HANG;
                        $transitions[] = static::TRANSITION_ACCEPT;
                        break;
                    case PaymentStates::STATE_CANCELED:
                        $transitions[] = static::TRANSITION_HANG;
                        $transitions[] = static::TRANSITION_ACCEPT;
                        break;
                }
            } elseif ($method->isOutstanding() || $method->isManual()) {
                if ($state === PaymentStates::STATE_CAPTURED) {
                    $transitions[] = static::TRANSITION_CANCEL;
                } else {
                    $transitions[] = static::TRANSITION_ACCEPT;
                }
            } else {
                if ($state === PaymentStates::STATE_CAPTURED) {
                    $transitions[] = static::TRANSITION_REFUND;
                }
                if (in_array($state, [PaymentStates::STATE_NEW, PaymentStates::STATE_PENDING], true)) {
                    //$diff = $payment->getUpdatedAt()->diff(new \DateTime());
                    //if (0 < $diff->days && !$diff->invert) {
                        $transitions[] = static::TRANSITION_CANCEL;
                    //}
                }
            }
        } else {
            //if ($method->isManual() && $state === PaymentStates::STATE_PENDING) {
            if (in_array($state, [PaymentStates::STATE_NEW, PaymentStates::STATE_PENDING], true)) {
                $transitions[] = static::TRANSITION_CANCEL;
            }
        }

        return $transitions;
    }

    /**
     * Returns whether the payment can be canceled by the user.
     *
     * @param PaymentInterface $payment
     *
     * @return bool
     */
    static public function isUserCancellable(PaymentInterface $payment)
    {
        return in_array(static::TRANSITION_CANCEL, static::getAvailableTransitions($payment), true);
    }

    /**
     * Disabled constructor.
     *
     * @codeCoverageIgnore
     */
    final private function __construct()
    {
    }
}
