<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Bridge\Symfony\Serializer\Normalizer;

use Ekyna\Component\Commerce\Invoice\Model\InvoiceInterface;
use Ekyna\Component\Resource\Bridge\Symfony\Serializer\ResourceNormalizer;

use function array_replace;

/**
 * Class InvoiceNormalizer
 * @package Ekyna\Component\Commerce\Bridge\Symfony\Serializer\Normalizer
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class InvoiceNormalizer extends ResourceNormalizer
{
    /**
     * @inheritDoc
     *
     * @param InvoiceInterface $object
     */
    public function normalize($object, string $format = null, array $context = [])
    {
        $data = parent::normalize($object, $format, $context);

        if (self::contextHasGroup(['Default', 'OrderInvoice'], $context)) {
            $sale = $object->getSale();

            $data = array_replace($data, [
                'number'      => $object->getNumber(),
                'company'     => $sale->getCompany(),
                'email'       => $sale->getEmail(),
                'first_name'  => $sale->getFirstName(),
                'last_name'   => $sale->getLastName(),
                'type'        => $object->getType(),
                'description' => $object->getDescription(),
                'comment'     => $object->getComment(),
            ]);
        } elseif (self::contextHasGroup(['Search'], $context)) {
            $sale = $object->getSale();

            $data = array_replace($data, [
                'number'      => $object->getNumber(),
                'sale_number' => $sale->getNumber(),
                'sale_id'     => $sale->getId(),
            ]);
        } elseif (self::contextHasGroup(['Summary'], $context)) {
            $lines = [];
            foreach ($object->getLines() as $line) {
                $lines[] = $this->normalizeObject($line, $format, $context);
            }

            $items = [];
            foreach ($object->getItems() as $item) {
                $items[] = $this->normalizeObject($item, $format, $context);
            }

            $invoiceAddress = $object->getCustomInvoiceAddress() ?: $object->getInvoiceAddress();
            $deliveryAddress = $object->getCustomDeliveryAddress() ?: $object->getDeliveryAddress();

            $data = array_replace($data, [
                'number'           => $object->getNumber(),
                'lines'            => $lines,
                'items'            => $items,
                'description'      => $object->getDescription(),
                'comment'          => $object->getComment(),
                'invoice_address'  => $invoiceAddress,
                'delivery_address' => $deliveryAddress,
                'margin'           => [
                    'net'   => [
                        'total'   => $object->getMargin()->getTotal(false)->toFixed(2),
                        'percent' => $object->getMargin()->getPercent(false)->toFixed(2),
                    ],
                    'gross' => [
                        'total'   => $object->getMargin()->getTotal(true)->toFixed(2),
                        'percent' => $object->getMargin()->getPercent(true)->toFixed(2),
                    ],
                ],
            ]);
        }

        return $data;
    }
}
