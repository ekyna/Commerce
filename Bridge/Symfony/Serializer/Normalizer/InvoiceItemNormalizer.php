<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Bridge\Symfony\Serializer\Normalizer;

use Ekyna\Component\Commerce\Invoice\Model\InvoiceItemInterface;
use Ekyna\Component\Resource\Bridge\Symfony\Serializer\ResourceNormalizer;

/**
 * Class InvoiceItemNormalizer
 * @package Ekyna\Component\Commerce\Bridge\Symfony\Serializer\Normalizer
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class InvoiceItemNormalizer extends ResourceNormalizer
{
    /**
     * @inheritDoc
     *
     * @param InvoiceItemInterface $object
     */
    public function normalize($object, string $format = null, array $context = [])
    {
        $data = [];

        if ($this->contextHasGroup('Summary', $context)) {
            $data = array_replace($data, [
                'designation' => $object->getDesignation(),
                'reference'   => $object->getReference(),
                'quantity'    => $object->getQuantity(),
            ]);
        }

        return $data;
    }
}
