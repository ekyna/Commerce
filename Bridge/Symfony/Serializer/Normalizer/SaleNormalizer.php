<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Bridge\Symfony\Serializer\Normalizer;

use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Common\Util\Money;
use Ekyna\Component\Commerce\Invoice\Model\InvoiceInterface;
use Ekyna\Component\Commerce\Invoice\Model\InvoiceSubjectInterface;
use Ekyna\Component\Commerce\Payment\Model\PaymentInterface;
use Ekyna\Component\Commerce\Payment\Model\PaymentStates;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentInterface;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentStates;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentSubjectInterface;
use Ekyna\Component\Resource\Bridge\Symfony\Serializer\ResourceNormalizer;

/**
 * Class SaleNormalizer
 * @package Ekyna\Component\Commerce\Bridge\Symfony\Serializer\Normalizer
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SaleNormalizer extends ResourceNormalizer
{
    /**
     * @inheritDoc
     *
     * @param SaleInterface $object
     */
    public function normalize($object, string $format = null, array $context = [])
    {
        if ($format === 'csv' && $this->contextHasGroup('TableExport', $context)) {
            return (string)$object;
        }

        $data = parent::normalize($object, $format, $context);

        if ($this->contextHasGroup(['Default', 'Cart', 'Order', 'Quote', 'Search'], $context)) {
            $data = array_replace($data, [
                'number'         => $object->getNumber(),
                'voucher_number' => $object->getVoucherNumber(),
                'email'          => $object->getEmail(),
                'company'        => $object->getCompany(),
                'first_name'     => $object->getFirstName(),
                'last_name'      => $object->getLastName(),
                'title'          => $object->getTitle(),
            ]);
        } elseif ($this->contextHasGroup('Summary', $context)) {
            $items = [];

            foreach ($object->getItems() as $item) {
                $items[] = $this->normalizeObject($item, $format, $context);
            }

            $precision = Money::getPrecision($currency = $object->getCurrency()->getCode());

            $data = array_replace($data, [
                'number'           => $object->getNumber(),
                'email'            => $object->getEmail(),
                'customer_group'   => $object->getCustomerGroup()->getName(),
                'company'          => $object->getCompany(),
                'company_number'   => $object->getCompanyNumber(),
                'first_name'       => $object->getFirstName(),
                'last_name'        => $object->getLastName(),
                'items'            => $items,
                'currency'         => $currency,
                'total'            => $object->getGrandTotal()->toFixed($precision),
                'description'      => $object->getDescription(),
                'comment'          => $object->getComment(),
                'preparation_note' => $object->getPreparationNote(),
                'payment_term'     => null,
                'outstanding_date' => null,
                'created_at'       => $object->getCreatedAt()->format('Y-m-d'),
                'state'            => $object->getState(),
                'payment_state'    => $object->getPaymentState(),
                'paid_total'       => $object->getPaidTotal()->toFixed($precision),
                'refunded_total'   => $object->getRefundedTotal()->toFixed($precision),
                'shipment_subject' => false,
                'invoice_subject'  => false,
                'payments'         => [],
                'refunds'          => [],
            ]);

            if (null !== $term = $object->getPaymentTerm()) {
                $data['payment_term'] = $term->getName();
            }

            if (null !== $date = $object->getOutstandingDate()) {
                $data['outstanding_date'] = $date->format('Y-m-d');
            }

            // Payments
            foreach ($object->getPayments(true) as $payment) {
                if (PaymentStates::isDeletableState($payment)) {
                    continue;
                }

                $data['payments'][] = $this->normalizePayment($payment);
            }

            // Refunds
            foreach ($object->getPayments(false) as $refund) {
                if (PaymentStates::isDeletableState($refund)) {
                    continue;
                }

                $data['refunds'][] = $this->normalizePayment($refund);
            }

            if ($object instanceof ShipmentSubjectInterface) {
                $data['shipment_subject'] = true;
                $data['shipment_state'] = $object->getShipmentState();

                // Shipments
                $data['shipments'] = [];
                foreach ($object->getShipments(true) as $shipment) {
                    if (ShipmentStates::isDeletableState($shipment->getState())) {
                        continue;
                    }

                    $data['shipments'][] = $this->normalizeShipment($shipment);
                }

                // Returns
                $data['returns'] = [];
                foreach ($object->getShipments(false) as $shipment) {
                    if (ShipmentStates::isDeletableState($shipment->getState())) {
                        continue;
                    }

                    $data['returns'][] = $this->normalizeShipment($shipment);
                }
            }

            if ($object instanceof InvoiceSubjectInterface) {
                $data['invoice_subject'] = !$object->isSample();
                $data['invoice_state'] = $object->getInvoiceState();
                $data['invoice_total'] = $object->getInvoiceTotal()->toFixed($precision);
                $data['credit_total'] = $object->getCreditTotal()->toFixed($precision);

                // Invoices
                $data['invoices'] = [];
                foreach ($object->getInvoices(true) as $invoice) {
                    $data['invoices'][] = $this->normalizeInvoice($invoice);
                }

                // Credits
                $data['credits'] = [];
                foreach ($object->getInvoices(false) as $credit) {
                    $data['credits'][] = $this->normalizeInvoice($credit);
                }
            }
        }

        return $data;
    }

    /**
     * Normalizes the payment.
     */
    protected function normalizePayment(PaymentInterface $payment): array
    {
        $completedAt = $payment->getCompletedAt();

        return [
            'number'       => $payment->getNumber(),
            'method'       => $payment->getMethod()->getName(),
            'state'        => $payment->getState(),
            'currency'     => $currency = $payment->getCurrency()->getCode(),
            'amount'       => $payment->getAmount()->toFixed(Money::getPrecision($currency)),
            'completed_at' => $completedAt ? $completedAt->format('Y-m-d') : null,
        ];
    }

    /**
     * Normalizes the shipment.
     */
    protected function normalizeShipment(ShipmentInterface $shipment): array
    {
        $shippedAt = $shipment->getShippedAt();

        return [
            'number'     => $shipment->getNumber(),
            'method'     => $shipment->getMethod()->getName(),
            'state'      => $shipment->getState(),
            'shipped_at' => $shippedAt ? $shippedAt->format('Y-m-d') : null,
        ];
    }

    /**
     * Normalizes the invoice.
     */
    protected function normalizeInvoice(InvoiceInterface $invoice): array
    {
        return [
            'number'      => $invoice->getNumber(),
            'currency'    => $invoice->getCurrency(),
            'grand_total' => Money::fixed($invoice->getRealGrandTotal(), $invoice->getCurrency()),
            'created_at'  => $invoice->getCreatedAt()->format('Y-m-d'),
        ];
    }
}
