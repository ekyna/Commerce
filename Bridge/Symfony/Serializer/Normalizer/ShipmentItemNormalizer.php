<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Bridge\Symfony\Serializer\Normalizer;

use Ekyna\Component\Commerce\Shipment\Model\ShipmentItemInterface;
use Ekyna\Component\Resource\Bridge\Symfony\Serializer\ResourceNormalizer;

/**
 * Class ShipmentItemNormalizer
 * @package Ekyna\Component\Commerce\Bridge\Symfony\Serializer\Normalizer
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ShipmentItemNormalizer extends ResourceNormalizer
{
    /**
     * @inheritDoc
     *
     * @param ShipmentItemInterface $object
     */
    public function normalize($object, string $format = null, array $context = [])
    {
        $data = [];

        if (self::contextHasGroup('Summary', $context)) {
            $saleItem = $object->getSaleItem();

            $data = array_replace($data, [
                'designation' => $saleItem->getDesignation(),
                'reference'   => $saleItem->getReference(),
                'quantity'    => $object->getQuantity(),
                'total'       => $saleItem->getTotalQuantity(),
                'level'       => $saleItem->getLevel(),
            ]);
        }

        return $data;
    }
}
