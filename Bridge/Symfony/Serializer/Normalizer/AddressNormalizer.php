<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Bridge\Symfony\Serializer\Normalizer;

use Ekyna\Component\Commerce\Common\Model\AddressInterface;
use Ekyna\Component\Commerce\Customer\Model\CustomerAddressInterface;
use Ekyna\Component\Resource\Bridge\Symfony\Serializer\ResourceNormalizer;
use libphonenumber\PhoneNumber;
use libphonenumber\PhoneNumberUtil;

/**
 * Class AddressNormalizer
 * @package Ekyna\Component\Commerce\Bridge\Symfony\Serializer\Normalizer
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class AddressNormalizer extends ResourceNormalizer
{
    private PhoneNumberUtil $phoneNumberUtil;


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
     * @inheritDoc
     *
     * @param AddressInterface $object
     */
    public function normalize($object, $format = null, array $context = [])
    {
        $data = parent::normalize($object, $format, $context);

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
                'company'        => $object->getCompany(),
                'gender'         => $object->getGender(),
                'first_name'     => $object->getFirstName(),
                'last_name'      => $object->getLastName(),
                'street'         => $object->getStreet(),
                'complement'     => $object->getComplement(),
                'supplement'     => $object->getSupplement(),
                'postal_code'    => $object->getPostalCode(),
                'city'           => $object->getCity(),
                'country_name'   => $object->getCountry()->getName(),
                'country'        => $object->getCountry()->getCode(),
                //'state'        => $address->getCity(),
                'phone'          => $this->normalizeObject($object->getPhone(), $format, $context),
                'phone_country'  => $this->phoneNumberCountry($object->getPhone()),
                'mobile'         => $this->normalizeObject($object->getMobile(), $format, $context),
                'mobile_country' => $this->phoneNumberCountry($object->getMobile()),
                'digicode1'      => $object->getDigicode1(),
                'digicode2'      => $object->getDigicode2(),
                'intercom'       => $object->getIntercom(),
            ]);

            if ($object instanceof CustomerAddressInterface) {
                $data['invoice_default'] = $object->isInvoiceDefault() ? 1 : 0;
                $data['delivery_default'] = $object->isDeliveryDefault() ? 1 : 0;
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
    private function phoneNumberCountry(PhoneNumber $phoneNumber = null): ?string
    {
        if ($phoneNumber) {
            return $this->phoneNumberUtil->getRegionCodeForNumber($phoneNumber);
        }

        return null;
    }
}
