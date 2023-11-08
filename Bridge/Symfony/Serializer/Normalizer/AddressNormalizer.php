<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Bridge\Symfony\Serializer\Normalizer;

use Ekyna\Component\Commerce\Common\Model\AddressInterface;
use Ekyna\Component\Commerce\Common\Transformer\ArrayToAddressTransformer;
use Ekyna\Component\Commerce\Customer\Model\CustomerAddressInterface;
use Ekyna\Component\Commerce\Shipment\Model\RelayPointInterface;
use Ekyna\Component\Resource\Bridge\Symfony\Serializer\ResourceNormalizer;
use Ekyna\Component\Resource\Model\ResourceInterface;
use libphonenumber\PhoneNumberFormat;
use libphonenumber\PhoneNumberUtil;

use function array_replace;

/**
 * Class AddressNormalizer
 * @package Ekyna\Component\Commerce\Bridge\Symfony\Serializer\Normalizer
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class AddressNormalizer extends ResourceNormalizer
{
    private const RESOURCE_GROUPS = [
        'CartAddress',
        'CustomerAddress',
        'Default',
        'OrderAddress',
        'QuoteAddress',
        'SupplierAddress',
    ];

    protected ArrayToAddressTransformer $transformer;
    protected PhoneNumberUtil           $phoneNumberUtil;

    public function __construct(ArrayToAddressTransformer $transformer, PhoneNumberUtil $phoneNumberUtil)
    {
        $this->transformer = $transformer;
        $this->phoneNumberUtil = $phoneNumberUtil;
    }

    /**
     * @inheritDoc
     *
     * @param AddressInterface $object
     */
    public function normalize($object, $format = null, array $context = [])
    {
        $data = [];
        $exclude = [];

        if (!self::contextHasGroup(self::RESOURCE_GROUPS, $context)) {
            $exclude = [
                'digicode1',
                'digicode2',
                'intercom',
                'information',
                'latitude',
                'longitude',
            ];
        } elseif ($object instanceof ResourceInterface) {
            $data = parent::normalize($object, $format, $context);
        }

        if ($asChoice = self::contextHasGroup('AddressChoice', $context)) {
            $exclude += ['phone', 'mobile'];
        }

        $data = array_replace($data, $this->transformer->transformAddress($object, $exclude));

        if ($asChoice) {
            if ($phone = $object->getPhone()) {
                $data['phone'] = $this->phoneNumberUtil->format($phone, PhoneNumberFormat::NATIONAL);
                $data['phone_country'] = $this->phoneNumberUtil->getRegionCodeForNumber($phone);
            }

            if ($mobile = $object->getMobile()) {
                $data['mobile'] = $this->phoneNumberUtil->format($mobile, PhoneNumberFormat::NATIONAL);
                $data['mobile_country'] = $this->phoneNumberUtil->getRegionCodeForNumber($mobile);
            }
        }

        if ($object instanceof CustomerAddressInterface) {
            $data['invoice_default'] = $object->isInvoiceDefault() ? 1 : 0;
            $data['delivery_default'] = $object->isDeliveryDefault() ? 1 : 0;
        }

        if ($object instanceof RelayPointInterface) {
            $data['number'] = $object->getNumber();
        }

        return $data;
    }

    /**
     * @inheritDoc
     *
     * @return AddressInterface
     */
    public function denormalize($data, string $type, string $format = null, array $context = [])
    {
        /* TODO (id) if (is_subclass_of($type, ResourceInterface::class, true)) {
            return parent::denormalize($data, $type, $format, $context);
        }*/

        $object = $this->transformer->transformArray($data, $type, ['phone', 'mobile']);

        if (isset($data['phone'])) {
            $region = $data['phone_country'] ?? null;
            $object->setPhone($this->phoneNumberUtil->parse($data['phone'], $region));
        }

        if (isset($data['mobile'])) {
            $region = $data['mobile_country'] ?? null;
            $object->setMobile($this->phoneNumberUtil->parse($data['mobile'], $region));
        }

        if (isset($data['number']) && $object instanceof RelayPointInterface) {
            $object->setNumber($data['number']);
        }

        return $object;
    }
}
