<?php

namespace Ekyna\Component\Commerce\Bridge\Doctrine\DependencyInjection;

use Ekyna\Component\Commerce\Cart;
use Ekyna\Component\Commerce\Customer;
use Ekyna\Component\Commerce\Order;
use Ekyna\Component\Commerce\Payment;
use Ekyna\Component\Commerce\Pricing;
use Ekyna\Component\Commerce\Product;
use Ekyna\Component\Commerce\Quote;
use Ekyna\Component\Commerce\Shipment;
use Ekyna\Component\Commerce\Supplier;

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
        // TODO remove unused/useless interfaces
        return [
            Cart\Model\CartInterface::class                  => Cart\Entity\Cart::class,
            Cart\Model\CartAddressInterface::class           => Cart\Entity\CartAddress::class,

            Customer\Model\CustomerInterface::class          => Customer\Entity\Customer::class,
            Customer\Model\CustomerAddressInterface::class   => Customer\Entity\CustomerAddress::class,

            Order\Model\OrderInterface::class                => Order\Entity\Order::class,
            Order\Model\OrderAddressInterface::class         => Order\Entity\OrderAddress::class,

            Payment\Model\PaymentMethodInterface::class      => Payment\Entity\PaymentMethod::class,

            Pricing\Model\TaxGroupInterface::class           => Pricing\Entity\TaxGroup::class,

            Product\Model\AttributeGroupInterface::class     => Product\Entity\AttributeGroup::class,
            Product\Model\AttributeInterface::class          => Product\Entity\Attribute::class,
            Product\Model\AttributeSetInterface::class       => Product\Entity\AttributeSet::class,
            Product\Model\AttributeSlotInterface::class      => Product\Entity\AttributeSlot::class,
            Product\Model\BundleChoiceInterface::class       => Product\Entity\BundleChoice::class,
            Product\Model\BundleChoiceRuleInterface::class   => Product\Entity\BundleChoiceRule::class,
            Product\Model\BundleSlotInterface::class         => Product\Entity\BundleSlot::class,
            Product\Model\OptionGroupInterface::class        => Product\Entity\OptionGroup::class,
            Product\Model\OptionInterface::class             => Product\Entity\Option::class,
            Product\Model\ProductInterface::class            => Product\Entity\Product::class,
            Product\Model\ProductStockUnitInterface::class   => Product\Entity\ProductStockUnit::class,

            Quote\Model\QuoteInterface::class                => Quote\Entity\Quote::class,
            Quote\Model\QuoteAddressInterface::class         => Quote\Entity\QuoteAddress::class,

            Shipment\Model\ShipmentMethodInterface::class    => Shipment\Entity\ShipmentMethod::class,

            Supplier\Model\SupplierInterface::class          => Supplier\Entity\Supplier::class,
            Supplier\Model\SupplierDeliveryInterface::class  => Supplier\Entity\SupplierDelivery::class,
            Supplier\Model\SupplierOrderInterface::class     => Supplier\Entity\SupplierOrder::class,
            Supplier\Model\SupplierProductInterface::class   => Supplier\Entity\SupplierProduct::class,
        ];
    }
}
