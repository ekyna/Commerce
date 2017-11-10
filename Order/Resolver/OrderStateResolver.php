<?php

namespace Ekyna\Component\Commerce\Order\Resolver;

use Ekyna\Component\Commerce\Common\Resolver\AbstractSaleStateResolver;
use Ekyna\Component\Commerce\Common\Resolver\StateResolverInterface;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Invoice\Model\InvoiceStates;
use Ekyna\Component\Commerce\Order\Model\OrderInterface;
use Ekyna\Component\Commerce\Order\Model\OrderStates;
use Ekyna\Component\Commerce\Payment\Model\PaymentStates;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentStates;

/**
 * Class OrderStateResolver
 * @package Ekyna\Component\Commerce\Order\Resolver
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class OrderStateResolver extends AbstractSaleStateResolver implements StateResolverInterface
{
    /**
     * @inheritdoc
     */
    public function resolve($order)
    {
        if (!$order instanceof OrderInterface) {
            throw new InvalidArgumentException("Expected instance of OrderInterface.");
        }

        parent::resolve($order);

        $paymentState = $order->getPaymentState();
        $shipmentState = $order->getShipmentState();
        $invoiceState = $order->getInvoiceState();

        // Order states
        if ($order->hasItems()) {
            // COMPLETED If fully Paid / Shipped / Invoiced
            if (
                PaymentStates::STATE_COMPLETED === $paymentState &&
                ShipmentStates::isShippedState($shipmentState) &&
                InvoiceStates::STATE_INVOICED === $invoiceState
            ) {
                return $this->setState($order, OrderStates::STATE_COMPLETED);
            }

            // COMPLETED If fully Refund / Returned / Credited
            if (
                PaymentStates::STATE_REFUNDED === $paymentState &&
                ShipmentStates::isReturnedState($shipmentState) &&
                InvoiceStates::STATE_CREDITED === $invoiceState
            ) {
                return $this->setState($order, OrderStates::STATE_COMPLETED);
            }

            // ACCEPTED If order has shipment(s) or invoice(s)
            if ($order->hasShipments() || $order->hasInvoices()) {
                return $this->setState($order, OrderStates::STATE_ACCEPTED);
            }

            // ACCEPTED If payment state is accepted or outstanding
            $acceptedStates = [
                PaymentStates::STATE_CAPTURED,
                PaymentStates::STATE_AUTHORIZED,
                PaymentStates::STATE_OUTSTANDING,
            ];
            if (in_array($paymentState, $acceptedStates, true)) {
                return $this->setState($order, OrderStates::STATE_ACCEPTED);
            }

            // PENDING If order has pending offline payments
            if (PaymentStates::STATE_PENDING === $paymentState) {
                return $this->setState($order, OrderStates::STATE_PENDING);
            }

            // REFUNDED If order has been refunded
            if (PaymentStates::STATE_REFUNDED === $paymentState) {
                return $this->setState($order, OrderStates::STATE_REFUNDED);
            }

            // FAILED If all payments have failed
            if (PaymentStates::STATE_FAILED === $paymentState) {
                return $this->setState($order, OrderStates::STATE_REFUSED);
            }

            // CANCELED If all payments have been canceled
            if (PaymentStates::STATE_CANCELED === $paymentState) {
                return $this->setState($order, OrderStates::STATE_CANCELED);
            }
        }

        // NEW (default)
        return $this->setState($order, OrderStates::STATE_NEW);
    }
}
