<?php

namespace Ekyna\Component\Commerce\Bridge\Symfony\Serializer\Normalizer;

use Ekyna\Component\Commerce\Customer\Model\CustomerInterface;
use Ekyna\Component\Resource\Serializer\AbstractResourceNormalizer;

/**
 * Class CustomerNormalizer
 * @package Ekyna\Component\Commerce\Bridge\Symfony\Serializer\Normalizer
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CustomerNormalizer extends AbstractResourceNormalizer
{
    /**
     * @inheritdoc
     */
    public function normalize($customer, $format = null, array $context = [])
    {
        $data = parent::normalize($customer, $format, $context);

        /** @var CustomerInterface $customer */
        $groups = isset($context['groups']) ? (array)$context['groups'] : [];

        if (in_array('Default', $groups) || in_array('Search', $groups)) {
            $parent = $customer->getParent();

            $data = array_replace($data, [
                'company'    => $customer->getCompany(),
                'email'      => $customer->getEmail(),
                'first_name' => $customer->getFirstName(),
                'last_name'  => $customer->getLastName(),
                'parent'     => $parent ? $parent->getId() : null,
                'vatValid'   => $customer->isVatValid(),
            ]);
        }

        return $data;
    }

    /**
     * @inheritdoc
     */
    public function denormalize($data, $class, $format = null, array $context = [])
    {
        $object = parent::denormalize($data, $class, $format, $context);

        throw new \Exception('Not yet implemented');
    }

    /**
     * @inheritdoc
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof CustomerInterface;
    }

    /**
     * @inheritdoc
     */
    public function supportsDenormalization($data, $type, $format = null)
    {
        return class_exists($type) && is_subclass_of($type, CustomerInterface::class);
    }
}
