<?php

namespace Ekyna\Component\Commerce\Bridge\Symfony\Serializer\Normalizer;

use Ekyna\Component\Commerce\Invoice\Model\InvoiceInterface;
use Ekyna\Component\Resource\Serializer\AbstractResourceNormalizer;

/**
 * Class InvoiceNormalizer
 * @package Ekyna\Component\Commerce\Bridge\Symfony\Serializer\Normalizer
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class InvoiceNormalizer extends AbstractResourceNormalizer
{
    /**
     * @inheritdoc
     *
     * @param InvoiceInterface $invoice
     */
    public function normalize($invoice, $format = null, array $context = [])
    {
        $data = parent::normalize($invoice, $format, $context);

        if ($this->contextHasGroup(['Default', 'OrderInvoice', 'Search'], $context)) {
            $sale = $invoice->getSale();

            $data = array_replace($data, [
                'number'      => $invoice->getNumber(),
                'company'     => $sale->getCompany(),
                'email'       => $sale->getEmail(),
                'first_name'  => $sale->getFirstName(),
                'last_name'   => $sale->getLastName(),
                'type'        => $invoice->getType(),
                'method'      => $invoice->getPaymentMethod()->getName(),
                'description' => $invoice->getDescription(),
                'comment'     => $invoice->getComment(),
            ]);
        } elseif ($this->contextHasGroup(['Summary'], $context)) {
            $items = [];

            foreach ($invoice->getLines() as $item) {
                $items[] = $this->normalizeObject($item, $format, $context);
            }

            $data = array_replace($data, [
                'items'       => $items,
                'description' => $invoice->getDescription(),
                'comment'     => $invoice->getComment(),
            ]);
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
        return $data instanceof InvoiceInterface;
    }

    /**
     * @inheritdoc
     */
    public function supportsDenormalization($data, $type, $format = null)
    {
        return class_exists($type) && is_subclass_of($type, InvoiceInterface::class);
    }
}
