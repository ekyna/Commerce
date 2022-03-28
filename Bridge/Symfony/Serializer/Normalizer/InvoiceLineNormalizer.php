<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Bridge\Symfony\Serializer\Normalizer;

use Ekyna\Component\Commerce\Invoice\Model\InvoiceLineInterface;
use Ekyna\Component\Resource\Bridge\Symfony\Serializer\ResourceNormalizer;

/**
 * Class InvoiceLineNormalizer
 * @package Ekyna\Component\Commerce\Bridge\Symfony\Serializer\Normalizer
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class InvoiceLineNormalizer extends ResourceNormalizer
{
    /**
     * @inheritDoc
     *
     * @param InvoiceLineInterface $object
     */
    public function normalize($object, string $format = null, array $context = [])
    {
        $data = [];

        if ($this->contextHasGroup('Summary', $context)) {
            $saleItem = $object->getSaleItem();

            $data = array_replace($data, [
                'designation' => $object->getDesignation(),
                'reference'   => $object->getReference(),
                'quantity'    => $object->getQuantity(),
                'total'       => $saleItem ? $saleItem->getTotalQuantity() : null,
                'level'       => $saleItem ? $saleItem->getLevel() : 0,
            ]);
        }

        return $data;
    }
}
