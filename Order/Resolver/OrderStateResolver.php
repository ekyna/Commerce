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
                InvoiceStates::STATE_COMPLETED === $invoiceState
            ) {
                return OrderStates::STATE_COMPLETED;
            }

            // ACCEPTED If outstanding accepted/expired amount
            if (0 < $sale->getOutstandingAccepted() || 0 < $sale->getOutstandingExpired()) {
                return OrderStates::STATE_ACCEPTED;
            }

            // REFUNDED If fully Refund / Returned / Credited
            $refundablePaymentStates = [
                PaymentStates::STATE_COMPLETED,
                PaymentStates::STATE_REFUNDED,
                PaymentStates::STATE_FAILED,
                PaymentStates::STATE_CANCELED,
                PaymentStates::STATE_NEW,
            ];
            $refundableShipmentStates = [
                ShipmentStates::STATE_COMPLETED,
                ShipmentStates::STATE_RETURNED,
                ShipmentStates::STATE_PENDING,
                ShipmentStates::STATE_CANCELED,
                ShipmentStates::STATE_NEW,
            ];
            if (
                InvoiceStates::STATE_CREDITED === $invoiceState &&
                in_array($paymentState, $refundablePaymentStates, true) &&
                in_array($shipmentState, $refundableShipmentStates, true)
            ) {
                return OrderStates::STATE_REFUNDED;
            }

            // ACCEPTED If order has shipment(s) or invoice(s).
            if ($sale->hasShipments() || $sale->hasInvoices()) {
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

        // NEW by default
        return OrderStates::STATE_NEW;
    }

    /**
     * @inheritDoc
     */
    protected function postStateResolution(SaleInterface $sale)
    {
        if (!$sale instanceof OrderInterface) {
            throw new InvalidArgumentException("Expected instance of " . OrderInterface::class);
        }

        if (in_array($sale->getState(), [OrderStates::STATE_CANCELED, OrderStates::STATE_REFUSED, OrderStates::STATE_REFUNDED], true)) {
            if (!in_array($sale->getShipmentState(), ShipmentStates::getStockableStates(), true)) {
                $sale->setShipmentState(ShipmentStates::STATE_CANCELED);
            }
            if (in_array($sale->getInvoiceState(), [InvoiceStates::STATE_NEW, InvoiceStates::STATE_PENDING], true)) {
                $sale->setInvoiceState(InvoiceStates::STATE_CANCELED);
            }
        }
    }
}
