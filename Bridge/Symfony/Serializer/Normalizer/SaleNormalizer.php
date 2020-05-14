<?php

namespace Ekyna\Component\Commerce\Bridge\Symfony\Serializer\Normalizer;

use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Invoice\Model\InvoiceInterface;
use Ekyna\Component\Commerce\Invoice\Model\InvoiceSubjectInterface;
use Ekyna\Component\Commerce\Payment\Model\PaymentInterface;
use Ekyna\Component\Commerce\Payment\Model\PaymentStates;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentInterface;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentStates;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentSubjectInterface;
use Ekyna\Component\Resource\Serializer\AbstractResourceNormalizer;

/**
 * Class SaleNormalizer
 * @package Ekyna\Component\Commerce\Bridge\Symfony\Serializer\Normalizer
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SaleNormalizer extends AbstractResourceNormalizer
{
    /**
     * @inheritdoc
     *
     * @param SaleInterface $sale
     */
    public function normalize($sale, $format = null, array $context = [])
    {
        if ($format === 'csv' && $this->contextHasGroup('TableExport', $context)) {
            return (string)$sale;
        }

        $data = parent::normalize($sale, $format, $context);

        if ($this->contextHasGroup(['Default', 'Cart', 'Order', 'Quote', 'Search'], $context)) {
            $data = array_replace($data, [
                'number'         => $sale->getNumber(),
                'voucher_number' => $sale->getVoucherNumber(),
                'email'          => $sale->getEmail(),
                'company'        => $sale->getCompany(),
                'first_name'     => $sale->getFirstName(),
                'last_name'      => $sale->getLastName(),
                'title'          => $sale->getTitle(),
            ]);
        } elseif ($this->contextHasGroup('Summary', $context)) {
            $items = [];

            foreach ($sale->getItems() as $item) {
                $items[] = $this->normalizeObject($item, $format, $context);
            }

            $data = array_replace($data, [
                'number'           => $sale->getNumber(),
                'email'            => $sale->getEmail(),
                'customer_group'   => $sale->getCustomerGroup()->getName(),
                'company'          => $sale->getCompany(),
                'company_number'   => $sale->getCompanyNumber(),
                'first_name'       => $sale->getFirstName(),
                'last_name'        => $sale->getLastName(),
                'items'            => $items,
                'currency'         => $sale->getCurrency()->getCode(),
                'total'            => $sale->getGrandTotal(),
                'description'      => $sale->getDescription(),
                'comment'          => $sale->getComment(),
                'preparation_note' => $sale->getPreparationNote(),
                'payment_term'     => null,
                'outstanding_date' => null,
                'created_at'       => $sale->getCreatedAt()->format('Y-m-d'),
                'state'            => $sale->getState(),
                'payment_state'    => $sale->getPaymentState(),
                'paid_total'       => $sale->getPaidTotal(),
                'refunded_total'   => $sale->getRefundedTotal(),
                'payments'         => [],
                'refunds'          => [],
            ]);

            if (null !== $term = $sale->getPaymentTerm()) {
                $data['payment_term'] = $term->getName();
            }

            if (null !== $date = $sale->getOutstandingDate()) {
                $data['outstanding_date'] = $date->format('Y-m-d');
            }

            // Payments
            foreach ($sale->getPayments(true) as $payment) {
                if (PaymentStates::isDeletableState($payment)) {
                    continue;
                }

                $data['payments'][] = $this->normalizePayment($payment);
            }

            // Refunds
            foreach ($sale->getPayments(false) as $refund) {
                if (PaymentStates::isDeletableState($refund)) {
                    continue;
                }

                $data['refunds'][] = $this->normalizePayment($refund);
            }

            if ($sale instanceof ShipmentSubjectInterface) {
                $data['shipment_state'] = $sale->getShipmentState();

                // Shipments
                $data['shipments'] = [];
                foreach ($sale->getShipments(true) as $shipment) {
                    if (ShipmentStates::isDeletableState($shipment->getState())) {
                        continue;
                    }

                    $data['shipments'][] = $this->normalizeShipment($shipment);
                }

                // Returns
                $data['returns'] = [];
                foreach ($sale->getShipments(false) as $shipment) {
                    if (ShipmentStates::isDeletableState($shipment->getState())) {
                        continue;
                    }

                    $data['returns'][] = $this->normalizeShipment($shipment);
                }
            }

            if ($sale instanceof InvoiceSubjectInterface) {
                $data['invoice_state'] = $sale->getInvoiceState();
                $data['invoice_total'] = $sale->getInvoiceTotal();
                $data['credit_total'] = $sale->getCreditTotal();

                // Invoices
                $data['invoices'] = [];
                foreach ($sale->getInvoices(true) as $invoice) {
                    $data['invoices'][] = $this->normalizeInvoice($invoice);
                }

                // Credits
                $data['credits'] = [];
                foreach ($sale->getInvoices(false) as $credit) {
                    $data['credits'][] = $this->normalizeInvoice($credit);
                }
            }
        }

        return $data;
    }

    /**
     * Normalizes the payment.
     *
     * @param PaymentInterface $payment
     *
     * @return array
     */
    protected function normalizePayment(PaymentInterface $payment): array
    {
        $completedAt = $payment->getCompletedAt();

        return [
            'number'       => $payment->getNumber(),
            'method'       => $payment->getMethod()->getName(),
            'state'        => $payment->getState(),
            'currency'     => $payment->getCurrency()->getCode(),
            'amount'       => $payment->getAmount(),
            'completed_at' => $completedAt ? $completedAt->format('Y-m-d') : null,
        ];
    }

    /**
     * Normalizes the shipment.
     *
     * @param ShipmentInterface $shipment
     *
     * @return array
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
     *
     * @param InvoiceInterface $invoice
     *
     * @return array
     */
    protected function normalizeInvoice(InvoiceInterface $invoice): array
    {
        return [
            'number'      => $invoice->getNumber(),
            'currency'    => $invoice->getCurrency(),
            'grand_total' => $invoice->getRealGrandTotal(),
            'created_at'  => $invoice->getCreatedAt()->format('Y-m-d'),
        ];
    }

    /**
     * @inheritdoc
     */
    public function denormalize($data, $class, $format = null, array $context = [])
    {
        //$object = parent::denormalize($data, $class, $format, $context);

        throw new \Exception('Not yet implemented');
    }

    /**
     * @inheritdoc
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof SaleInterface;
    }

    /**
     * @inheritdoc
     */
    public function supportsDenormalization($data, $type, $format = null)
    {
        return class_exists($type) && is_subclass_of($type, SaleInterface::class);
    }
}
