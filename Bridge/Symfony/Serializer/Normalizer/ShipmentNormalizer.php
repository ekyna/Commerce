<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Bridge\Symfony\Serializer\Normalizer;

use Ekyna\Component\Commerce\Shipment\Model\ShipmentInterface;
use Ekyna\Component\Resource\Bridge\Symfony\Serializer\ResourceNormalizer;

/**
 * Class ShipmentNormalizer
 * @package Ekyna\Component\Commerce\Bridge\Symfony\Serializer\Normalizer
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ShipmentNormalizer extends ResourceNormalizer
{
    /**
     * @inheritDoc
     *
     * @param ShipmentInterface $object
     */
    public function normalize($object, string $format = null, array $context = [])
    {
        $data = parent::normalize($object, $format, $context);

        if ($this->contextHasGroup(['Default', 'OrderShipment'], $context)) {
            $sale = $object->getSale();

            $data = array_replace($data, [
                'number'          => $object->getNumber(),
                'company'         => $sale->getCompany(),
                'email'           => $sale->getEmail(),
                'first_name'      => $sale->getFirstName(),
                'last_name'       => $sale->getLastName(),
                'type'            => $object->isReturn() ? 'return' : 'shipment',
                'method'          => $object->getMethod()->getName(),
                'tracking_number' => $object->getTrackingNumber(),
                'description'     => $object->getDescription(),
            ]);
        } elseif ($this->contextHasGroup('Summary', $context)) {
            $items = [];
            $parcels = [];

            foreach ($object->getItems() as $item) {
                $items[] = $this->normalizeObject($item, $format, $context);
            }
            foreach ($object->getParcels() as $parcel) {
                $parcels[] = $this->normalizeObject($parcel, $format, $context);
            }

            $data = array_replace($data, [
                'items'           => $items,
                'parcels'         => $parcels,
                'description'     => $object->getDescription(),
                'tracking_number' => $object->getTrackingNumber(),
                'valorization'    => $object->getValorization(),
            ]);
        }

        return $data;
    }
}
