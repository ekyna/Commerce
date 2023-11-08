<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Bridge\Symfony\Serializer\Normalizer;

use Ekyna\Component\Commerce\Supplier\Model\SupplierOrderInterface;
use Ekyna\Component\Resource\Bridge\Symfony\Serializer\ResourceNormalizer;

/**
 * Class SupplierOrderNormalizer
 * @package Ekyna\Component\Commerce\Bridge\Symfony\Serializer\Normalizer
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SupplierOrderNormalizer extends ResourceNormalizer
{
    /**
     * @inheritDoc
     *
     * @param SupplierOrderInterface $object
     */
    public function normalize($object, string $format = null, array $context = [])
    {
        $data = parent::normalize($object, $format, $context);

        if (self::contextHasGroup('Search', $context)) {
            $carrier = $object->getCarrier();

            $data = array_replace($data, [
                'number'      => $object->getNumber(),
                'supplier'    => [
                    'id'   => $object->getSupplier()->getId(),
                    'name' => $object->getSupplier()->getName(),
                ],
                'carrier'     => $carrier ? [
                    'id'   => $carrier->getId(),
                    'name' => $carrier->getName(),
                ] : null,
                'description' => $object->getDescription(),
            ]);
        }

        if (self::contextHasGroup('Summary', $context)) {
            $items = [];
            foreach ($object->getItems() as $item) {
                $items[] = $this->normalizeObject($item, $format, $context);
            }

            $data = array_replace($data, [
                'items'       => $items,
                'description' => $object->getDescription(),
            ]);
        }

        return $data;
    }
}
