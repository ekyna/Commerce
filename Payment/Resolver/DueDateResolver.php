<?php

namespace Ekyna\Component\Commerce\Payment\Resolver;

use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Invoice\Model as Invoice;
use Ekyna\Component\Commerce\Payment\Model as Payment;
use Ekyna\Component\Commerce\Shipment\Model as Shipment;

/**
 * Class DueDateResolver
 * @package Ekyna\Component\Commerce\Payment\Resolver
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class DueDateResolver implements DueDateResolverInterface
{
    /**
     * @inheritDoc
     */
    public function isInvoiceDue(Invoice\InvoiceInterface $invoice): bool
    {
        // Not due if no due date.
        if (null === $date = $invoice->getDueDate()) {
            return false;
        }

        // Not due if paid
        if ($invoice->isPaid()) {
            return false;
        }

        // Not due if fully credit with no payment
        if ($invoice->getSale()->isPaid()) {
            return false;
        }

        // Due if due date is past
        $diff = $date->diff((new \DateTime())->setTime(0, 0, 0, 0));
        if (0 < $diff->days && !$diff->invert) {
            return true;
        }

        return false;
    }

    /**
     * @inheritDoc
     */
    public function resolveInvoiceDueDate(Invoice\InvoiceInterface $invoice): ?\DateTime
    {
        if (null === $sale = $invoice->getSale()) {
            return null;
        }

        if (!$sale instanceof Invoice\InvoiceSubjectInterface) {
            return null;
        }
        if (!$sale instanceof Shipment\ShipmentSubjectInterface) {
            return null;
        }

        if ($invoice->isCredit()) {
            return clone $invoice->getCreatedAt();
        }

        if (null === $term = $sale->getPaymentTerm()) {
            return clone $invoice->getCreatedAt();
        }

        $from = null;
        switch ($term->getTrigger()) {
            case Payment\PaymentTermTriggers::TRIGGER_SHIPPED:
                // Use invoice's linked shipment if shipped
                if (null !== $shipment = $invoice->getShipment()) {
                    if (Shipment\ShipmentStates::STATE_SHIPPED === $shipment->getState()) {
                        $from = $shipment->getShippedAt();
                        break;
                    }
                }

                // Use invoice creation date if sale if fully or partially shipped
                if (Shipment\ShipmentStates::isStockableState($sale->getShipmentState())) {
                    $from = $invoice->getCreatedAt();
                }

                break;

            case Payment\PaymentTermTriggers::TRIGGER_FULLY_SHIPPED:
                // Use sale's shipped date if fully shipped
                if ($sale->getShipmentState() !== Shipment\ShipmentStates::STATE_COMPLETED) {
                    break;
                }

                $from = $sale->getShippedAt(true);
                break;

            case Payment\PaymentTermTriggers::TRIGGER_INVOICED:
                // Ue invoice's creation date
                $from = $invoice->getCreatedAt();
                break;

            case Payment\PaymentTermTriggers::TRIGGER_FULLY_INVOICED:
                // Use sale's invoiced date if fully invoiced
                if ($sale->getInvoiceState() === Invoice\InvoiceStates::STATE_COMPLETED) {
                    $from = $sale->getInvoicedAt(true);
                }
                break;
        }

        if (null === $from) {
            return null;
        }

        return $this->applyTermToDate($term, $from);
    }

    /**
     * @inheritDoc
     */
    public function resolveSaleDueDate(SaleInterface $sale): ?\DateTime
    {
        if (!$sale instanceof Invoice\InvoiceSubjectInterface) {
            return null;
        }
        if (!$sale instanceof Shipment\ShipmentSubjectInterface) {
            return null;
        }

        if (null === $term = $sale->getPaymentTerm()) {
            return null;
        }

        $from = null;
        switch ($term->getTrigger()) {
            case Payment\PaymentTermTriggers::TRIGGER_SHIPPED:
                $from = $sale->getShippedAt();
                break;

            case Payment\PaymentTermTriggers::TRIGGER_FULLY_SHIPPED:
                if ($sale->getShipmentState() === Shipment\ShipmentStates::STATE_COMPLETED) {
                    $from = $sale->getShippedAt(true);
                }
                break;

            case Payment\PaymentTermTriggers::TRIGGER_INVOICED:
                $from = $sale->getInvoicedAt();
                break;

            case Payment\PaymentTermTriggers::TRIGGER_FULLY_INVOICED:
                if ($sale->getInvoiceState() === Invoice\InvoiceStates::STATE_COMPLETED) {
                    $from = $sale->getInvoicedAt(true);
                }
                break;
        }

        if (null === $from) {
            return null;
        }

        return $this->applyTermToDate($term, $from);
    }

    /**
     * Apply the payment term's delay to the given date
     *
     * @param Payment\PaymentTermInterface $term
     * @param \DateTime            $date
     *
     * @return \DateTime
     */
    protected function applyTermToDate(Payment\PaymentTermInterface $term, \DateTime $date): \DateTime
    {
        $date = clone $date;
        $date->setTime(23, 59, 59, 999999);
        $date->modify(sprintf('+%s days', $term->getDays()));
        if ($term->getEndOfMonth()) {
            $date->modify('last day of this month');
        }

        return $date;
    }
}
