<?php

namespace Ekyna\Component\Commerce\Bridge\Symfony\Serializer\Normalizer;

use Ekyna\Component\Commerce\Common\Model\AddressInterface;
use Ekyna\Component\Commerce\Customer\Model\CustomerAddressInterface;
use Ekyna\Component\Resource\Serializer\AbstractResourceNormalizer;
use libphonenumber\PhoneNumberUtil;
use libphonenumber\PhoneNumber;

/**
 * Class AddressNormalizer
 * @package Ekyna\Component\Commerce\Bridge\Symfony\Serializer\Normalizer
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class AddressNormalizer extends AbstractResourceNormalizer
{
    /**
     * @var PhoneNumberUtil
     */
    private $phoneNumberUtil;


    /**
     * Constructor.
     *
     * @param PhoneNumberUtil $phoneNumberUtil
     */
    public function __construct(PhoneNumberUtil $phoneNumberUtil)
    {
        $this->phoneNumberUtil = $phoneNumberUtil;
    }

    /**
     * @inheritdoc
     *
     * @param AddressInterface $address
     */
    public function normalize($address, $format = null, array $context = [])
    {
        $data = parent::normalize($address, $format, $context);

        $groups = [
            'Address',
            'CartAddress',
            'CustomerAddress',
            'Default',
            'OrderAddress',
            'QuoteAddress',
            'ShipmentAddress',
            'SupplierAddress',
        ];

        if ($this->contextHasGroup($groups, $context)) {
            $data = array_replace($data, [
                'company'        => $address->getCompany(),
                'gender'         => $address->getGender(),
                'first_name'     => $address->getFirstName(),
                'last_name'      => $address->getLastName(),
                'street'         => $address->getStreet(),
                'complement'     => $address->getComplement(),
                'supplement'     => $address->getSupplement(),
                'postal_code'    => $address->getPostalCode(),
                'city'           => $address->getCity(),
                'country_name'   => $address->getCountry()->getName(),
                'country'        => $address->getCountry()->getCode(),
                //'state'        => $address->getCity(),
                'phone'          => $this->normalizeObject($address->getPhone(), $format, $context),
                'phone_country'  => $this->phoneNumberCountry($address->getPhone()),
                'mobile'         => $this->normalizeObject($address->getMobile(), $format, $context),
                'mobile_country' => $this->phoneNumberCountry($address->getMobile()),
                'digicode1'      => $address->getDigicode1(),
                'digicode2'      => $address->getDigicode2(),
                'intercom'       => $address->getIntercom(),
            ]);

            if ($address instanceof CustomerAddressInterface) {
                $data['invoice_default'] = $address->isInvoiceDefault() ? 1 : 0;
                $data['delivery_default'] = $address->isDeliveryDefault() ? 1 : 0;
            }
        }

        return $data;
    }

    /**
     * Returns the region code for the given phone number.
     *
     * @param PhoneNumber|null $phoneNumber
     *
     * @return string|null
     */
    private function phoneNumberCountry(PhoneNumber $phoneNumber = null)
    {
        if ($phoneNumber) {
            return $this->phoneNumberUtil->getRegionCodeForNumber($phoneNumber);
        }

        return null;
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
