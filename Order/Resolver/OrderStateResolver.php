<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Order\Resolver;

use Ekyna\Component\Commerce\Common\Resolver\AbstractSaleStateResolver;
use Ekyna\Component\Commerce\Common\Resolver\StateResolverInterface;
use Ekyna\Component\Commerce\Exception\UnexpectedTypeException;
use Ekyna\Component\Commerce\Invoice\Model\InvoiceStates;
use Ekyna\Component\Commerce\Order\Model\OrderInterface;
use Ekyna\Component\Commerce\Order\Model\OrderStates;
use Ekyna\Component\Commerce\Payment\Model\PaymentStates;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentStates;

use function in_array;

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
        if (!$subject->hasItems()) {
            return OrderStates::STATE_NEW;
        }

        $paymentState = $subject->getPaymentState();
        $shipmentState = $subject->getShipmentState();
        $invoiceState = $subject->getInvoiceState();

        // Sample sale case
        if ($subject->isSample()) {
            // COMPLETED If fully returned or released
            if (ShipmentStates::STATE_RETURNED === $shipmentState) {
                return OrderStates::STATE_COMPLETED;
            } elseif($subject->isReleased()) {
                if (ShipmentStates::isDeletableState($subject)) {
                    $subject->setShipmentState(ShipmentStates::STATE_CANCELED);
                } else {
                    $subject->setShipmentState(ShipmentStates::STATE_COMPLETED);
                }

                return OrderStates::STATE_COMPLETED;
            }

            // ACCEPTED
            return OrderStates::STATE_ACCEPTED;
        }

        // COMPLETED If fully Paid / Shipped / Invoiced
        if (
            PaymentStates::STATE_COMPLETED === $paymentState
            && ShipmentStates::STATE_COMPLETED === $shipmentState
            && InvoiceStates::STATE_COMPLETED === $invoiceState
        ) {
            return OrderStates::STATE_COMPLETED;
        }

        // ACCEPTED If outstanding accepted/expired amount
        if (0 < $subject->getOutstandingAccepted() || 0 < $subject->getOutstandingExpired()) {
            return OrderStates::STATE_ACCEPTED;
        }

        // REFUNDED If fully Credited
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
            InvoiceStates::STATE_CREDITED === $invoiceState
            && in_array($paymentState, $refundablePaymentStates, true)
            && in_array($shipmentState, $refundableShipmentStates, true)
        ) {
            $this->cancelPaymentState($subject);
            $this->cancelShipmentState($subject);

            return OrderStates::STATE_REFUNDED;
        }

        // REFUNDED If fully Refund
        $cancelableInvoiceStates = [
            InvoiceStates::STATE_CREDITED,
            InvoiceStates::STATE_CANCELED,
            InvoiceStates::STATE_NEW,
        ];
        $cancelableShipmentStates = [
            ShipmentStates::STATE_RETURNED,
            ShipmentStates::STATE_CANCELED,
            ShipmentStates::STATE_NEW,
        ];
        if (
            PaymentStates::STATE_REFUNDED === $paymentState
            && in_array($invoiceState, $cancelableInvoiceStates, true)
            && in_array($shipmentState, $cancelableShipmentStates, true)
        ) {
            $this->cancelShipmentState($subject);
            $this->cancelInvoiceState($subject);

            return OrderStates::STATE_REFUNDED;
        }

        // CANCELLED If all payments canceled
        if (
            PaymentStates::STATE_CANCELED === $paymentState
            && in_array($invoiceState, $cancelableInvoiceStates, true)
            && in_array($shipmentState, $cancelableShipmentStates, true)
        ) {
            $this->cancelShipmentState($subject);
            $this->cancelInvoiceState($subject);

            return OrderStates::STATE_CANCELED;
        }

        // ACCEPTED If order has paid or shipment(s) or invoice(s).
        if (0 < $subject->getPaidTotal() || $subject->hasInvoices()) {
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

        // PENDING If payment state is pending
        if (PaymentStates::STATE_PENDING === $paymentState || 0 < $subject->getPendingTotal()) {
            return OrderStates::STATE_PENDING;
        }

        // ACCEPTED If payment state is accepted, outstanding or pending
        $acceptedStates = [
            PaymentStates::STATE_COMPLETED,
            PaymentStates::STATE_AUTHORIZED,
            PaymentStates::STATE_CAPTURED,
            PaymentStates::STATE_PAYEDOUT,
            PaymentStates::STATE_OUTSTANDING,
        ];
        if (in_array($paymentState, $acceptedStates, true)) {
            return OrderStates::STATE_ACCEPTED;
        }

        // FAILED If all payments have failed
        if (PaymentStates::STATE_FAILED === $paymentState) {
            $this->cancelShipmentState($subject);
            $this->cancelInvoiceState($subject);

            return OrderStates::STATE_REFUSED;
        }

        // NEW by default
        return OrderStates::STATE_NEW;
    }

    private function cancelPaymentState(OrderInterface $order): void
    {
        if (!PaymentStates::isDeletableState($order->getPaymentState())) {
            return;
        }

        $order->setPaymentState(PaymentStates::STATE_CANCELED);
    }

    private function cancelShipmentState(OrderInterface $order): void
    {
        if (!ShipmentStates::isDeletableState($order)) {
            return;
        }

        $order->setShipmentState(ShipmentStates::STATE_CANCELED);
    }

    private function cancelInvoiceState(OrderInterface $order): void
    {
        if (!InvoiceStates::isDeletableState($order->getInvoiceState())) {
            return;
        }

        $order->setInvoiceState(InvoiceStates::STATE_CANCELED);
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
