<?php

namespace Ekyna\Component\Commerce\Bridge\Doctrine\DependencyInjection;

use Ekyna\Component\Commerce\Customer;
use Ekyna\Component\Commerce\Order;
use Ekyna\Component\Commerce\Payment;
use Ekyna\Component\Commerce\Pricing;
use Ekyna\Component\Commerce\Product;
use Ekyna\Component\Commerce\Shipment;

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
            Customer\Model\CustomerInterface::class        => Customer\Entity\Customer::class,
            Customer\Model\CustomerAddressInterface::class => Customer\Entity\CustomerAddress::class,

            Order\Model\OrderInterface::class              => Order\Entity\Order::class,
            Order\Model\OrderAddressInterface::class       => Order\Entity\OrderAddress::class,

            Payment\Model\PaymentInterface::class          => Payment\Entity\Payment::class,
            Payment\Model\PaymentMethodInterface::class    => Payment\Entity\PaymentMethod::class,

            Pricing\Model\TaxGroupInterface::class         => Pricing\Entity\TaxGroup::class,

            Product\Model\AttributeGroupInterface::class   => Product\Entity\AttributeGroup::class,
            Product\Model\AttributeInterface::class        => Product\Entity\Attribute::class,
            Product\Model\AttributeSetInterface::class     => Product\Entity\AttributeSet::class,
            Product\Model\AttributeSlotInterface::class    => Product\Entity\AttributeSlot::class,
            Product\Model\BundleChoiceInterface::class     => Product\Entity\BundleChoice::class,
            Product\Model\BundleChoiceRuleInterface::class => Product\Entity\BundleChoiceRule::class,
            Product\Model\BundleSlotInterface::class       => Product\Entity\BundleSlot::class,
            Product\Model\OptionGroupInterface::class      => Product\Entity\OptionGroup::class,
            Product\Model\OptionInterface::class           => Product\Entity\Option::class,
            Product\Model\ProductInterface::class          => Product\Entity\Product::class,

            Shipment\Model\ShipmentInterface::class        => Shipment\Entity\Shipment::class,
            Shipment\Model\ShipmentItemInterface::class    => Shipment\Entity\ShipmentItem::class,
            Shipment\Model\ShipmentMethodInterface::class  => Shipment\Entity\ShipmentMethod::class,
        ];
    }
}
