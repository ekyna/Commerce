<?php

namespace Ekyna\Component\Commerce\Bridge\Doctrine\DependencyInjection;

use Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Type;
use Ekyna\Component\Commerce\Cart;
use Ekyna\Component\Commerce\Common;
use Ekyna\Component\Commerce\Customer;
use Ekyna\Component\Commerce\Order;
use Ekyna\Component\Commerce\Payment;
use Ekyna\Component\Commerce\Pricing;
use Ekyna\Component\Commerce\Quote;
use Ekyna\Component\Commerce\Shipment;
use Ekyna\Component\Commerce\Supplier;
use Ekyna\Component\Commerce\Support;
use Ekyna\Component\Commerce\Stock;

/**
 * Class DoctrineBundleMapping
 * @package Ekyna\Component\Commerce\Bridge\Doctrine\DependencyInjection
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class DoctrineBundleMapping
{
    /**
     * Returns the doctrine ORM mapping config for the doctrine bundle (doctrine.dbal.types)
     *
     * @return array
     */
    static function buildTypesConfiguration()
    {
        return [
            Type\OpeningHours::NAME => [
                'class'     => Type\OpeningHours::class,
                'commented' => true,
            ],
        ];
    }

    /**
     * Returns the doctrine ORM mapping config for the doctrine bundle (doctrine.orm.mappings)
     *
     * @return array
     */
    static function buildMappingConfiguration()
    {
        return [
            'EkynaCommerce' => [
                'type'      => 'xml',
                'dir'       => realpath(__DIR__ . '/../ORM/Resources/mapping'),
                'is_bundle' => false,
                'prefix'    => 'Ekyna\Component\Commerce',
                'alias'     => 'EkynaCommerce',
            ],
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
            Cart\Model\CartInterface::class                 => Cart\Entity\Cart::class,
            Cart\Model\CartAddressInterface::class          => Cart\Entity\CartAddress::class,

            Common\Model\CouponInterface::class             => Common\Entity\Coupon::class,

            Customer\Model\CustomerInterface::class         => Customer\Entity\Customer::class,
            Customer\Model\CustomerGroupInterface::class    => Customer\Entity\CustomerGroup::class,
            Customer\Model\CustomerAddressInterface::class  => Customer\Entity\CustomerAddress::class,

            Order\Model\OrderInterface::class               => Order\Entity\Order::class,
            Order\Model\OrderAddressInterface::class        => Order\Entity\OrderAddress::class,

            Payment\Model\PaymentMethodInterface::class     => Payment\Entity\PaymentMethod::class,
            Payment\Model\PaymentTermInterface::class       => Payment\Entity\PaymentTerm::class,

            Quote\Model\QuoteInterface::class               => Quote\Entity\Quote::class,
            Quote\Model\QuoteAddressInterface::class        => Quote\Entity\QuoteAddress::class,

            Shipment\Model\ShipmentMethodInterface::class   => Shipment\Entity\ShipmentMethod::class,

            Supplier\Model\SupplierInterface::class         => Supplier\Entity\Supplier::class,
            Supplier\Model\SupplierAddressInterface::class  => Supplier\Entity\SupplierAddress::class,
            Supplier\Model\SupplierDeliveryInterface::class => Supplier\Entity\SupplierDelivery::class,
            Supplier\Model\SupplierOrderInterface::class    => Supplier\Entity\SupplierOrder::class,
            Supplier\Model\SupplierProductInterface::class  => Supplier\Entity\SupplierProduct::class,

            Support\Model\TicketInterface::class            => Support\Entity\Ticket::class,
            Support\Model\TicketMessageInterface::class     => Support\Entity\TicketMessage::class,

            Stock\Model\WarehouseInterface::class           => Stock\Entity\Warehouse::class,
        ];
    }
}
