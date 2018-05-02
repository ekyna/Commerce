<?php

namespace Ekyna\Component\Commerce\Bridge\Symfony\Serializer\Normalizer;

use Ekyna\Component\Commerce\Customer\Model\CustomerGroupInterface;
use Ekyna\Component\Resource\Serializer\AbstractResourceNormalizer;

/**
 * Class CustomerGroupNormalizer
 * @package Ekyna\Component\Commerce\Bridge\Symfony\Serializer\Normalizer
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CustomerGroupNormalizer extends AbstractResourceNormalizer
{
    /**
     * @inheritdoc
     *
     * @param CustomerGroupInterface $group
     */
    public function normalize($group, $format = null, array $context = [])
    {
        $groups = isset($context['groups']) ? (array)$context['groups'] : [];

        if ($format === 'csv' && in_array('TableExport', $groups)) {
            return (string)$group;
        }

        return parent::normalize($group, $format, $context);
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
        return $data instanceof CustomerGroupInterface;
    }

    /**
     * @inheritdoc
     */
    public function supportsDenormalization($data, $type, $format = null)
    {
        return class_exists($type) && is_subclass_of($type, CustomerGroupInterface::class);
    }
}