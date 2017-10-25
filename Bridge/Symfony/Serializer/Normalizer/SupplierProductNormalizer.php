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
     */
    public function normalize($product, $format = null, array $context = [])
    {
        $data = parent::normalize($product, $format, $context);

        /** @var SupplierProductInterface $product */
        $groups = isset($context['groups']) ? (array)$context['groups'] : [];

        if (in_array('Default', $groups) || in_array('Search', $groups)) {
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
