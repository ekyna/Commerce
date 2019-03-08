<?php

namespace Ekyna\Component\Commerce\Bridge\Symfony\Serializer\Normalizer;

use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Invoice\Model\InvoiceSubjectInterface;
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
                'number'     => $sale->getNumber(),
                'company'    => $sale->getCompany(),
                'email'      => $sale->getEmail(),
                'first_name' => $sale->getFirstName(),
                'last_name'  => $sale->getLastName(),
            ]);
        } elseif ($this->contextHasGroup('Summary', $context)) {
            $items = [];

            foreach ($sale->getItems() as $item) {
                $items[] = $this->normalizeObject($item, $format, $context);
            }

            $data = array_replace($data, [
                'items'            => $items,
                'total'            => $sale->getGrandTotal(),
                'description'      => $sale->getDescription(),
                'comment'          => $sale->getComment(),
                'preparation_note' => $sale->getPreparationNote(),
                'payment_term'     => null,
                'outstanding_date' => null,
                'created_at'       => $sale->getCreatedAt()->format('Y-m-d H:i:s'),
                'shipped_at'       => null,
                'invoiced_at'      => null,
            ]);
            if (null !== $term = $sale->getPaymentTerm()) {
                $data['payment_term'] = $term->getName();
            }
            if (null !== $date = $sale->getOutstandingDate()) {
                $data['outstanding_date'] = $date->format('Y-m-d H:i:s');
            }
            if ($sale instanceof ShipmentSubjectInterface && null !== $date = $sale->getShippedAt()) {
                $data['shipped_at'] = $date->format('Y-m-d H:i:s');
            }
            if ($sale instanceof InvoiceSubjectInterface && null !== $date = $sale->getInvoicedAt()) {
                $data['invoiced_at'] = $date->format('Y-m-d H:i:s');

                $data['invoices'] = [];
                foreach ($sale->getInvoices(true) as $invoice) {
                    $data['invoices'][] = [
                        'number'      => $invoice->getNumber(),
                        'grand_total' => $invoice->getGrandTotal(),
                        'created_at'  => $invoice->getCreatedAt()->format('Y-m-d H:i:s'),
                    ];
                }
            }
        }

        return $data;
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