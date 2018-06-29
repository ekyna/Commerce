<?php

namespace Ekyna\Component\Commerce\Common\Listener;

use Ekyna\Component\Commerce\Common\Model\NotificationTypes;
use Ekyna\Component\Commerce\Order\Model\OrderInterface;
use Ekyna\Component\Commerce\Order\Model\OrderStates;

/**
 * Class OrderNotifyListener
 * @package Ekyna\Component\Commerce\Common\Listener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class OrderNotifyListener extends AbstractNotifyListener
{
    /**
     * Post persist event handler.
     *
     * @param OrderInterface $order
     */
    public function postPersist(OrderInterface $order)
    {
        $this->watch($order);
    }

    /**
     * Post update event handler.
     *
     * @param OrderInterface $order
     */
    public function postUpdate(OrderInterface $order)
    {
        $this->watch($order);
    }

    /**
     * Order watcher.
     *
     * @param OrderInterface $order
     */
    protected function watch(OrderInterface $order)
    {
        // Abort if notify disabled or sample order
        if (!$order->isNotifyEnabled() || $order->isSample()) {
            return;
        }

        // Abort if sale has notification of type 'SALE_ACCEPTED'
        if ($order->hasNotifications(NotificationTypes::ORDER_ACCEPTED)) {
            return;
        }

        // Abort if state has not changed for 'ACCEPTED'
        if (!$this->didStateChangeTo($order, OrderStates::STATE_ACCEPTED)) {
            return;
        }

        $this->notify(NotificationTypes::ORDER_ACCEPTED, $order);
    }
}
