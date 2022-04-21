<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Bridge\Symfony\Serializer\Normalizer;

use Ekyna\Component\Commerce\Shipment\Model\ShipmentParcelInterface;
use Ekyna\Component\Resource\Bridge\Symfony\Serializer\ResourceNormalizer;

/**
 * Class ShipmentParcelNormalizer
 * @package Ekyna\Component\Commerce\Bridge\Symfony\Serializer\Normalizer
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ShipmentParcelNormalizer extends ResourceNormalizer
{
    /**
     * @inheritDoc
     *
     * @param ShipmentParcelInterface $object
     */
    public function normalize($object, string $format = null, array $context = [])
    {
        $data = [
            'id' => $object->getId(),
        ];

        if ($this->contextHasGroup('Summary', $context)) {
            $data = array_replace($data, [
                'weight'          => $object->getWeight()->toFixed(3),
                'valorization'    => $object->getValorization(),
                'tracking_number' => $object->getTrackingNumber(),
            ]);
        }

        return $data;
    }
}
