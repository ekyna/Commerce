<?php

namespace Ekyna\Component\Commerce\Bridge\Symfony\Serializer\Normalizer;

use Ekyna\Component\Commerce\Common\Model\AddressInterface;
use Ekyna\Component\Commerce\Customer\Model\CustomerAddressInterface;
use Ekyna\Component\Resource\Serializer\AbstractResourceNormalizer;

/**
 * Class AddressNormalizer
 * @package Ekyna\Component\Commerce\Bridge\Symfony\Serializer\Normalizer
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class AddressNormalizer extends AbstractResourceNormalizer
{
    /**
     * @inheritdoc
     */
    public function normalize($address, $format = null, array $context = [])
    {
        $data = parent::normalize($address, $format, $context);

        /** @var AddressInterface $address */
        $groups = isset($context['groups']) ? (array)$context['groups'] : [];

        if (in_array('Default', $groups)) {
            $data = array_replace($data, [
                'company'     => $address->getCompany(),
                'gender'      => $address->getGender(),
                'first_name'  => $address->getFirstName(),
                'last_name'   => $address->getLastName(),
                'street'      => $address->getStreet(),
                'complement'  => $address->getComplement(),
                'supplement'  => $address->getSupplement(),
                'postal_code' => $address->getPostalCode(),
                'city'        => $address->getCity(),
                'country'     => $address->getCountry()->getId(),
                //'state'        => $address->getCity(),
                'phone'       => $this->normalizeObject($address->getPhone(), $format, $context),
                'mobile'      => $this->normalizeObject($address->getMobile(), $format, $context),
            ]);

            if ($address instanceof CustomerAddressInterface) {
                $data['invoice_default'] = $address->isInvoiceDefault() ? 1 : 0;
                $data['delivery_default'] = $address->isDeliveryDefault() ? 1 : 0;
            }
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
        return $data instanceof AddressInterface;
    }

    /**
     * @inheritdoc
     */
    public function supportsDenormalization($data, $type, $format = null)
    {
        return class_exists($type) && is_subclass_of($type, AddressInterface::class);
    }
}
