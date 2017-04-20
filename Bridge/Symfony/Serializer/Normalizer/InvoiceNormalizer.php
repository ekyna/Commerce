<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Bridge\Symfony\Serializer\Normalizer;

use Ekyna\Component\Commerce\Invoice\Model\InvoiceInterface;
use Ekyna\Component\Resource\Bridge\Symfony\Serializer\ResourceNormalizer;

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

        if ($this->contextHasGroup(['Default', 'OrderInvoice', 'Search'], $context)) {
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
        } elseif ($this->contextHasGroup(['Summary'], $context)) {
            $lines = [];
            foreach ($object->getLines() as $line) {
                $lines[] = $this->normalizeObject($line, $format, $context);
            }
            $items = [];
            foreach ($object->getItems() as $item) {
                $items[] = $this->normalizeObject($item, $format, $context);
            }

            $data = array_replace($data, [
                'lines'       => $lines,
                'items'       => $items,
                'description' => $object->getDescription(),
                'comment'     => $object->getComment(),
            ]);
        }

        return $data;
    }
}
