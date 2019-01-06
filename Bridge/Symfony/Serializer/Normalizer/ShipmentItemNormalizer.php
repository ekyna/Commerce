<?php

namespace Ekyna\Component\Commerce\Bridge\Symfony\Serializer\Normalizer;

use Ekyna\Component\Commerce\Shipment\Model\ShipmentItemInterface;
use Ekyna\Component\Resource\Serializer\AbstractResourceNormalizer;

/**
 * Class ShipmentItemNormalizer
 * @package Ekyna\Component\Commerce\Bridge\Symfony\Serializer\Normalizer
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ShipmentItemNormalizer extends AbstractResourceNormalizer
{
    /**
     * @inheritdoc
     *
     * @param ShipmentItemInterface $item
     */
    public function normalize($item, $format = null, array $context = [])
    {
        $data = [];

        if ($this->contextHasGroup('Summary', $context)) {
            $saleItem = $item->getSaleItem();

            $data = array_replace($data, [
                'designation'      => $saleItem->getDesignation(),
                'reference'        => $saleItem->getReference(),
                'quantity'         => $item->getQuantity(),
                'total'            => $saleItem->getTotalQuantity(),
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
        return $data instanceof ShipmentItemInterface;
    }

    /**
     * @inheritdoc
     */
    public function supportsDenormalization($data, $type, $format = null)
    {
        return class_exists($type) && is_subclass_of($type, ShipmentItemInterface::class);
    }
}
