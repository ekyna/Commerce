<?php

namespace Ekyna\Component\Commerce\Bridge\Symfony\Serializer\Normalizer;

use Ekyna\Component\Commerce\Supplier\Model\SupplierOrderInterface;
use Ekyna\Component\Resource\Serializer\AbstractResourceNormalizer;

/**
 * Class SupplierOrderNormalizer
 * @package Ekyna\Component\Commerce\Bridge\Symfony\Serializer\Normalizer
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SupplierOrderNormalizer extends AbstractResourceNormalizer
{
    /**
     * @inheritdoc
     *
     * @param SupplierOrderInterface $order
     */
    public function normalize($order, $format = null, array $context = [])
    {
        $data = parent::normalize($order, $format, $context);

        if ($this->contextHasGroup('Summary', $context)) {
            $items = [];
            foreach ($order->getItems() as $item) {
                $items[] = $this->normalizeObject($item, $format, $context);
            }

            $data = array_replace($data, [
                'items'       => $items,
                'description' => $order->getDescription(),
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
        return $data instanceof SupplierOrderInterface;
    }

    /**
     * @inheritdoc
     */
    public function supportsDenormalization($data, $type, $format = null)
    {
        return class_exists($type) && is_subclass_of($type, SupplierOrderInterface::class);
    }
}