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
    public function postPersist(OrderPaymentInterface $payment)
    {
        $this->watch($payment);
    }

    /**
     * Post update event handler.
     *
     * @param OrderPaymentInterface $payment
     */
    public function postUpdate(OrderPaymentInterface $payment)
    {
        $this->watch($payment);
    }

    /**
     * Payment watcher.
     *
     * @param OrderPaymentInterface $payment
     */
    protected function watch(OrderPaymentInterface $payment)
    {
        $order = $payment->getOrder();

        // Abort if notify disabled or sample order
        if (!$order->isAutoNotify() || $order->isSample()) {
            return;
        }

        // Abort if not manual/offline payment
        if (!$payment->getMethod()->isManual()) {
            return;
        }

        // Abort if payment state has not changed for 'CAPTURED'
        if (!$this->didStateChangeTo($payment, PaymentStates::STATE_CAPTURED)) {
            return;
        }

        // Abort if sale has notification of type 'PAYMENT_CAPTURED' with same payment number
        /** @var \Ekyna\Component\Commerce\Order\Model\OrderNotificationInterface $n */
        foreach ($order->getNotifications() as $n) {
            if ($n->getType() !== NotificationTypes::PAYMENT_CAPTURED) {
                continue;
            }

            if ($n->hasData('payment') && $n->getData('payment') === $payment->getNumber()) {
                return;
            }
        }

        $this->notify(NotificationTypes::PAYMENT_CAPTURED, $payment);
    }
}
