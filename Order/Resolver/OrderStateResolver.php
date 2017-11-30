<?php

namespace Ekyna\Component\Commerce\Order\Resolver;

use Ekyna\Component\Commerce\Common\Model\SaleInterface;
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
    protected function resolveState(SaleInterface $sale)
    {
        if (!$sale instanceof OrderInterface) {
            throw new InvalidArgumentException("Expected instance of " . OrderInterface::class);
        }

        $paymentState = $sale->getPaymentState();
        $shipmentState = $sale->getShipmentState();
        $invoiceState = $sale->getInvoiceState();

        if ($sale->hasItems()) {
            // Sample sale case
            if ($sale->isSample()) {
                // COMPLETED If fully returned
                if (ShipmentStates::STATE_RETURNED === $shipmentState) {
                    return OrderStates::STATE_COMPLETED;
                }

                // ACCEPTED
                return OrderStates::STATE_ACCEPTED;
            }

            // COMPLETED If fully Paid / Shipped / Invoiced
            if (
                PaymentStates::STATE_COMPLETED === $paymentState &&
                ShipmentStates::STATE_COMPLETED === $shipmentState &&
                InvoiceStates::STATE_INVOICED === $invoiceState
            ) {
                return OrderStates::STATE_COMPLETED;
            }

            // COMPLETED If fully Refund / Returned / Credited
            if (
                PaymentStates::STATE_REFUNDED === $paymentState &&
                ShipmentStates::STATE_RETURNED === $shipmentState &&
                InvoiceStates::STATE_CREDITED === $invoiceState
            ) {
                return OrderStates::STATE_COMPLETED;
            }

            // ACCEPTED If order has shipment(s), invoice(s) or accepted outstanding.
            if ($sale->hasShipments() || $sale->hasInvoices() || 0 < $sale->getOutstandingAccepted()) {
                return OrderStates::STATE_ACCEPTED;
            }

            // ACCEPTED If payment state is accepted, outstanding or pending
            $acceptedStates = [
                PaymentStates::STATE_COMPLETED,
                PaymentStates::STATE_CAPTURED,
                PaymentStates::STATE_AUTHORIZED,
                PaymentStates::STATE_OUTSTANDING,
                PaymentStates::STATE_PENDING,
            ];
            if (in_array($paymentState, $acceptedStates, true)) {
                return OrderStates::STATE_ACCEPTED;
            }

            // PENDING If order has pending offline payments
            if (PaymentStates::STATE_PENDING === $paymentState) {
                return OrderStates::STATE_PENDING;
            }

            // REFUNDED If order has been refunded
            if (PaymentStates::STATE_REFUNDED === $paymentState) {
                return OrderStates::STATE_REFUNDED;
            }

            // FAILED If all payments have failed
            if (PaymentStates::STATE_FAILED === $paymentState) {
                return OrderStates::STATE_REFUSED;
            }

            // CANCELED If all payments have been canceled
            if (PaymentStates::STATE_CANCELED === $paymentState) {
                return OrderStates::STATE_CANCELED;
            }
        }

        return OrderStates::STATE_NEW;
    }
}
