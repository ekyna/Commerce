<?php

namespace Ekyna\Component\Commerce\Common\Listener;

use Ekyna\Component\Commerce\Common\Model\NotificationTypes;
use Ekyna\Component\Commerce\Order\Model\OrderPaymentInterface;
use Ekyna\Component\Commerce\Payment\Model\PaymentStates;

/**
 * Class PaymentNotifyListener
 * @package Ekyna\Component\Commerce\Common\Listener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class PaymentNotifyListener extends AbstractNotifyListener
{
    /**
     * Post persist event handler.
     *
     * @param OrderPaymentInterface $payment
     */
    public function postPersist(OrderPaymentInterface $payment): void
    {
        $this->watch($payment);
    }

    /**
     * Post update event handler.
     *
     * @param OrderPaymentInterface $payment
     */
    public function postUpdate(OrderPaymentInterface $payment): void
    {
        $this->watch($payment);
    }

    /**
     * Payment watcher.
     *
     * @param OrderPaymentInterface $payment
     */
    protected function watch(OrderPaymentInterface $payment): void
    {
        // Abort if payment is refund
        if ($payment->isRefund()) {
            return;
        }

        $order = $payment->getOrder();

        // Abort if notify disabled or sample order
        if (!$order->isAutoNotify() || $order->isSample()) {
            return;
        }

        // Abort if not manual/offline payment
        if (!$payment->getMethod()->isManual()) {
            return;
        }

        // If payment state has changed for 'AUTHORIZED'
        if ($this->didStateChangeTo($payment, PaymentStates::STATE_AUTHORIZED)) {
            $this->notifyState($payment, NotificationTypes::PAYMENT_AUTHORIZED, PaymentStates::STATE_AUTHORIZED);

            return;
        }

        // If payment state has changed for 'CAPTURED'
        if ($this->didStateChangeTo($payment, PaymentStates::STATE_CAPTURED)) {
            $this->notifyState($payment, NotificationTypes::PAYMENT_CAPTURED, PaymentStates::STATE_CAPTURED);
        }
    }

    /**
     * Sends the payment notification.
     *
     * @param OrderPaymentInterface $payment
     * @param string                $type
     * @param string                $state
     */
    protected function notifyState(OrderPaymentInterface $payment, string $type, string $state): void
    {
        $order = $payment->getOrder();

        // Abort if no custom message is defined
        $message = $payment->getMethod()->getMessageByState($state);
        if (empty($message->translate($order->getLocale())->getContent())) {
            return;
        }

        // Abort if sale has notification of the same type with same payment number
        if ($this->hasNotification($order, $type, 'payment', $payment->getNumber())) {
            return;
        }

        $this->notify($type, $payment);
    }
}
