<?php

namespace Ekyna\Component\Commerce\Bridge\Symfony\Serializer\Normalizer;

use Ekyna\Component\Commerce\Supplier\Model\SupplierProductInterface;
use Ekyna\Component\Resource\Serializer\AbstractResourceNormalizer;

/**
 * Class SupplierProductNormalizer
 * @package Ekyna\Component\Commerce\Bridge\Symfony\Serializer\Normalizer
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SupplierProductNormalizer extends AbstractResourceNormalizer
{
    /**
     * @inheritdoc
     *
     * @param SupplierProductInterface $product
     */
    public function normalize($product, $format = null, array $context = [])
    {
        $data = parent::normalize($product, $format, $context);

        if ($this->contextHasGroup(['Default', 'SupplierProduct', 'Search'], $context)) {
            $data = array_replace($data, [
                'designation' => $product->getDesignation(),
                'reference'   => $product->getReference(),
                'net_price'   => $product->getNetPrice(),
                'currency'    => $product->getSupplier()->getCurrency()->getCode(),
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
        return $data instanceof SupplierProductInterface;
    }

    /**
     * @inheritdoc
     */
    public function supportsDenormalization($data, $type, $format = null)
    {
        return class_exists($type) && is_subclass_of($type, SupplierProductInterface::class);
    }
}
