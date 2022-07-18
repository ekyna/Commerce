<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Common\Listener;

use Ekyna\Component\Commerce\Common\Model\NotificationTypes;
use Ekyna\Component\Commerce\Invoice\Model\InvoiceStates;
use Ekyna\Component\Commerce\Order\Model\OrderInvoiceInterface;

/**
 * Class InvoiceNotifyListener
 * @package Ekyna\Component\Commerce\Common\Listener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class InvoiceNotifyListener extends AbstractNotifyListener
{
    /**
     * Post persist event handler.
     */
    public function postPersist(OrderInvoiceInterface $invoice): void
    {
        $this->watch($invoice);
    }

    /**
     * Invoice watcher.
     */
    protected function watch(OrderInvoiceInterface $invoice): void
    {
        $order = $invoice->getOrder();

        // Abort if notify disabled
        if (!$order->isAutoNotify()) {
            return;
        }

        // If credit
        if ($invoice->isCredit()) {
            // TODO
            return;
        }

        // Abort if sale has notification of type 'INVOICE_COMPLETE' with same invoice number
        if ($this->hasNotification($order, NotificationTypes::INVOICE_COMPLETE, 'invoice', $invoice->getNumber())) {
            return;
        }
        // Abort if sale has notification of type 'INVOICE_PARTIAL' with same invoice number
        if ($this->hasNotification($order, NotificationTypes::INVOICE_PARTIAL, 'invoice', $invoice->getNumber())) {
            return;
        }

        $type = NotificationTypes::INVOICE_COMPLETE;
        if ($order->getInvoiceState() !== InvoiceStates::STATE_COMPLETED) {
            $type = NotificationTypes::INVOICE_PARTIAL;
        }

        $this->notify($type, $invoice);
    }
}
