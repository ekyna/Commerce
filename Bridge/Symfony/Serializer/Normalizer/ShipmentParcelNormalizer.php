<?php

namespace Ekyna\Component\Commerce\Bridge\Symfony\Serializer\Normalizer;

use Ekyna\Component\Commerce\Shipment\Model\ShipmentParcelInterface;
use Ekyna\Component\Resource\Serializer\AbstractResourceNormalizer;

/**
 * Class ShipmentParcelNormalizer
 * @package Ekyna\Component\Commerce\Bridge\Symfony\Serializer\Normalizer
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ShipmentParcelNormalizer extends AbstractResourceNormalizer
{
    /**
     * @inheritdoc
     *
     * @param ShipmentParcelInterface $parcel
     */
    public function normalize($parcel, $format = null, array $context = [])
    {
        $data = [];

        if ($this->contextHasGroup('Summary', $context)) {
            $data = array_replace($data, [
                'weight'         => $parcel->getWeight(),
                'valorization'   => $parcel->getValorization(),
                'trackingNumber' => $parcel->getTrackingNumber(),
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
        return $data instanceof ShipmentParcelInterface;
    }

    /**
     * @inheritdoc
     */
    public function supportsDenormalization($data, $type, $format = null)
    {
        return class_exists($type) && is_subclass_of($type, ShipmentParcelInterface::class);
    }
}
