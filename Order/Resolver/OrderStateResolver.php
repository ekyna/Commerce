<?php

namespace Ekyna\Component\Commerce\Order\Resolver;

use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Common\Resolver\AbstractSaleStateResolver;
use Ekyna\Component\Commerce\Common\Resolver\StateResolverInterface;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Exception\UnexpectedTypeException;
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
     * @inheritDoc
     *
     * @param OrderInterface $subject
     */
    protected function resolveState(object $subject): string
    {
        $paymentState = $subject->getPaymentState();
        $shipmentState = $subject->getShipmentState();
        $invoiceState = $subject->getInvoiceState();

        if ($subject->hasItems()) {
            // Sample sale case
            if ($subject->isSample()) {
                // COMPLETED If fully returned or released
                if ($subject->isReleased() || ShipmentStates::STATE_RETURNED === $shipmentState) {
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
            if (0 < $subject->getOutstandingAccepted() || 0 < $subject->getOutstandingExpired()) {
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

            // ACCEPTED If order has paid or pending total or shipment(s) or invoice(s).
            if (0 < $subject->getPaidTotal() || 0 < $subject->getPendingTotal() || $subject->hasInvoices()) {
                return OrderStates::STATE_ACCEPTED;
            }

            // ACCEPTED If shipped
            $acceptedShipmentStates = [
                ShipmentStates::STATE_PREPARATION,
                ShipmentStates::STATE_PARTIAL,
                ShipmentStates::STATE_COMPLETED,
                ShipmentStates::STATE_PENDING,
                ShipmentStates::STATE_READY,
            ];
            if ($subject->hasShipments() && in_array($shipmentState, $acceptedShipmentStates, true)) {
                return OrderStates::STATE_ACCEPTED;
            }

            // ACCEPTED If payment state is accepted, outstanding or pending
            $acceptedStates = [
                PaymentStates::STATE_COMPLETED,
                PaymentStates::STATE_AUTHORIZED,
                PaymentStates::STATE_CAPTURED,
                PaymentStates::STATE_PAYEDOUT,
                PaymentStates::STATE_OUTSTANDING,
                PaymentStates::STATE_PENDING,
            ];
            if (in_array($paymentState, $acceptedStates, true)) {
                return OrderStates::STATE_ACCEPTED;
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
    protected function postStateResolution(SaleInterface $sale): void
    {
        if (!$sale instanceof OrderInterface) {
            throw new InvalidArgumentException("Expected instance of " . OrderInterface::class);
        }

        if (!in_array($sale->getState(), [
            OrderStates::STATE_CANCELED,
            OrderStates::STATE_REFUSED,
            OrderStates::STATE_REFUNDED,
        ], true)) {
            return;
        }

        if (!in_array($sale->getShipmentState(), ShipmentStates::getStockableStates(false), true)) {
            $sale->setShipmentState(ShipmentStates::STATE_CANCELED);
        }

        if ($sale->getInvoiceState() === InvoiceStates::STATE_NEW) {
            $sale->setInvoiceState(InvoiceStates::STATE_CANCELED);
        }
    }

    /**
     * @inheritDoc
     */
    protected function supports(object $subject): void
    {
        if (!$subject instanceof OrderInterface) {
            throw new UnexpectedTypeException($subject, OrderInterface::class);
        }
    }
}
