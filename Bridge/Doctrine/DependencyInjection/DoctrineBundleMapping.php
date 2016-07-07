<?php

namespace Ekyna\Component\Commerce\Bridge\Doctrine\DependencyInjection;

/**
 * Class DoctrineBundleMapping
 * @package Ekyna\Component\Commerce\Bridge\Doctrine\DependencyInjection
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class DoctrineBundleMapping
{
    /**
     * Returns the config to may be append to doctrine.orm.mappings
     *
     * @return array
     */
    static function buildMappingConfiguration()
    {
        return [
            'type'      => 'xml',
            'dir'       => realpath(__DIR__ . '/../ORM/Resources/mapping'),
            'is_bundle' => false,
            'prefix'    => 'Ekyna\Component\Commerce',
            'alias'     => 'EkynaCommerce',
        ];
    }

    /**
     * Returns the default models implementations.
     *
     * @return array
     */
    static function getDefaultImplementations()
    {
        return [
            'Ekyna\Component\Commerce\Customer\Model\CustomerInterface' =>
                'Ekyna\Component\Commerce\Customer\Entity\Customer',
            'Ekyna\Component\Commerce\Customer\Model\CustomerAddressInterface' =>
                'Ekyna\Component\Commerce\Customer\Entity\CustomerAddress',

            'Ekyna\Component\Commerce\Order\Model\OrderInterface' =>
                'Ekyna\Component\Commerce\Order\Entity\Order',
            'Ekyna\Component\Commerce\Order\Model\OrderAddressInterface' =>
                'Ekyna\Component\Commerce\Order\Entity\OrderAddress',

            'Ekyna\Component\Commerce\Payment\Model\PaymentInterface' =>
                'Ekyna\Component\Commerce\Payment\Entity\Payment',
            'Ekyna\Component\Commerce\Payment\Model\PaymentMethodInterface' =>
                'Ekyna\Component\Commerce\Payment\Entity\PaymentMethod',

            'Ekyna\Component\Commerce\Shipment\Model\ShipmentInterface' =>
                'Ekyna\Component\Commerce\Shipment\Entity\Shipment',
            'Ekyna\Component\Commerce\Shipment\Model\ShipmentItemInterface' =>
                'Ekyna\Component\Commerce\Shipment\Entity\ShipmentItem',
            'Ekyna\Component\Commerce\Shipment\Model\ShipmentMethodInterface' =>
                'Ekyna\Component\Commerce\Shipment\Entity\ShipmentMethod',
        ];
    }
}
