<?php

namespace Ekyna\Component\Commerce\Bridge\Symfony\Serializer\Normalizer;

use Ekyna\Component\Commerce\Supplier\Model\SupplierProductInterface;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;

/**
 * Class SupplierProductNormalizer
 * @package Ekyna\Component\Commerce\Supplier\Serializer\Normalizer
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SupplierProductNormalizer extends AbstractObjectNormalizer
{
    /**
     * @inheritdoc
     */
    protected function extractAttributes($object, $format = null, array $context = [])
    {
        $stop = true;

        return [
            'supplier'
        ];
        // TODO: Implement extractAttributes() method.
    }

    /**
     * @inheritdoc
     */
    protected function getAttributeValue($object, $attribute, $format = null, array $context = [])
    {
        $stop = true;

        // TODO: Implement getAttributeValue() method.
    }

    /**
     * @inheritdoc
     */
    protected function setAttributeValue($object, $attribute, $value, $format = null, array $context = [])
    {
        $stop = true;

        // TODO: Implement setAttributeValue() method.
    }

    /**
     * @inheritDoc
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof SupplierProductInterface;
    }

    /**
     * @inheritDoc
     */
    public function supportsDenormalization($data, $type, $format = null)
    {
        return in_array(SupplierProductInterface::class, class_implements($type));
    }
}
