<?php

namespace Ekyna\Component\Commerce\Bridge\Symfony\Serializer\Normalizer;

use Ekyna\Component\Commerce\Shipment\Model\ShipmentInterface;
use Ekyna\Component\Resource\Serializer\AbstractResourceNormalizer;

/**
 * Class ShipmentNormalizer
 * @package Ekyna\Component\Commerce\Bridge\Symfony\Serializer\Normalizer
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ShipmentNormalizer extends AbstractResourceNormalizer
{
    /**
     * @inheritdoc
     *
     * @param ShipmentInterface $shipment
     */
    public function normalize($shipment, $format = null, array $context = [])
    {
        $data = parent::normalize($shipment, $format, $context);

        if ($this->contextHasGroup(['Default', 'OrderShipment'], $context)) {
            $sale = $shipment->getSale();

            $data = array_replace($data, [
                'number'          => $shipment->getNumber(),
                'company'         => $sale->getCompany(),
                'email'           => $sale->getEmail(),
                'first_name'      => $sale->getFirstName(),
                'last_name'       => $sale->getLastName(),
                'type'            => $shipment->isReturn() ? 'return' : 'shipment',
                'method'          => $shipment->getMethod()->getName(),
                'tracking_number' => $shipment->getTrackingNumber(),
                'description'     => $shipment->getDescription(),
            ]);
        } elseif ($this->contextHasGroup('Summary', $context)) {
            $items = [];
            $parcels = [];

            foreach ($shipment->getItems() as $item) {
                $items[] = $this->normalizeObject($item, $format, $context);
            }
            foreach ($shipment->getParcels() as $parcel) {
                $parcels[] = $this->normalizeObject($parcel, $format, $context);
            }

            $data = array_replace($data, [
                'items'           => $items,
                'parcels'         => $parcels,
                'description'     => $shipment->getDescription(),
                'tracking_number' => $shipment->getTrackingNumber(),
                'valorization'    => $shipment->getValorization(),
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
        return $data instanceof ShipmentInterface;
    }

    /**
     * @inheritdoc
     */
    public function supportsDenormalization($data, $type, $format = null)
    {
        return class_exists($type) && is_subclass_of($type, ShipmentInterface::class);
    }
}
