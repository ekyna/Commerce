<?php

declare(strict_types=1);

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
     */
    public function postPersist(OrderInterface $order): void
    {
        $this->watch($order);
    }

    /**
     * Post update event handler.
     */
    public function postUpdate(OrderInterface $order): void
    {
        $this->watch($order);
    }

    /**
     * Order watcher.
     */
    protected function watch(OrderInterface $order): void
    {
        // Abort if notify disabled or sample order
        if (!$order->isAutoNotify() || $order->isSample()) {
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
