<?php

namespace Ekyna\Component\Commerce\Tests;

use Acme\Product\Entity as Acme;
use DateTime;
use Ekyna\Component\Commerce\Common\Context\Context;
use Ekyna\Component\Commerce\Common\Context\ContextInterface;
use Ekyna\Component\Commerce\Common\Entity as CommonE;
use Ekyna\Component\Commerce\Common\Model as CommonM;
use Ekyna\Component\Commerce\Customer\Entity as CustomerE;
use Ekyna\Component\Commerce\Document\Model\DocumentLineTypes;
use Ekyna\Component\Commerce\Exception\UnexpectedTypeException;
use Ekyna\Component\Commerce\Order\Entity as OrderE;
use Ekyna\Component\Commerce\Order\Model as OrderM;
use Ekyna\Component\Commerce\Payment\Model\PaymentStates;
use Ekyna\Component\Commerce\Pricing\Entity as PricingE;
use Ekyna\Component\Commerce\Pricing\Model\VatDisplayModes;
use Ekyna\Component\Commerce\Shipment\Entity as ShipmentE;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentAddress;
use Ekyna\Component\Commerce\Stock\Entity as StockE;
use Ekyna\Component\Commerce\Stock\Model as StockM;
use Ekyna\Component\Commerce\Stock\Resolver\StockUnitStateResolver;
use Ekyna\Component\Commerce\Stock\Resolver\StockUnitStateResolverInterface;
use Ekyna\Component\Commerce\Subject\Model\SubjectInterface;
use Ekyna\Component\Commerce\Subject\Model\SubjectRelativeInterface;
use Ekyna\Component\Commerce\Supplier\Entity as SupplierE;
use InvalidArgumentException;
use LogicException;
use RuntimeException;
use Symfony\Component\Intl\Intl;
use Symfony\Component\Yaml\Yaml;

/**
 * Class Fixture
 * @package Ekyna\Component\Commerce\Tests
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class Fixture
{
    public const COUNTRY_FR = 'FR';
    public const COUNTRY_ES = 'ES';
    public const COUNTRY_US = 'US';

    public const CURRENCY_EUR = 'EUR';
    public const CURRENCY_USD = 'USD';
    public const CURRENCY_GBP = 'GBP';

    public const TAX_FR_NORMAL        = 'fr_normal';
    public const TAX_FR_INTERMEDIATE  = 'fr_intermediate';
    public const TAX_FR_REDUCED       = 'fr_reduced';
    public const TAX_FR_SUPER_REDUCED = 'fr_super_reduced';

    public const TAX_GROUP_NORMAL        = 'normal';
    public const TAX_GROUP_INTERMEDIATE  = 'intermediate';
    public const TAX_GROUP_REDUCED       = 'reduced';
    public const TAX_GROUP_SUPER_REDUCED = 'super_reduced';
    public const TAX_GROUP_EXEMPT        = 'exempt';

    public const TAX_RULE_FR_FR     = 'fr_fr';
    public const TAX_RULE_FR_EU_B2B = 'fr_eu_b2b';
    public const TAX_RULE_FR_EU_B2C = 'fr_eu_b2c';
    public const TAX_RULE_FR_WORLD  = 'fr_world';

    public const CUSTOMER_GROUP_DEFAULT  = 'customer_group_default';
    public const CUSTOMER_GROUP_BUSINESS = 'customer_group_business';

    private const CUSTOMER_GROUPS_MAP = [
        self::CUSTOMER_GROUP_DEFAULT  => ['name' => 'Customers', 'default' => true],
        self::CUSTOMER_GROUP_BUSINESS => ['name' => 'Business', 'business' => true],
    ];

    public const PAYMENT_METHOD_DEFAULT     = 'payment_method_default';
    public const PAYMENT_METHOD_MANUAL      = 'payment_method_manual';
    public const PAYMENT_METHOD_CREDIT      = 'payment_method_credit';
    public const PAYMENT_METHOD_OUTSTANDING = 'payment_method_outstanding';

    private const PAYMENT_METHODS_MAP = [
        self::PAYMENT_METHOD_DEFAULT     => [],
        self::PAYMENT_METHOD_MANUAL      => ['manual' => true],
        self::PAYMENT_METHOD_CREDIT      => ['credit' => true],
        self::PAYMENT_METHOD_OUTSTANDING => ['outstanding' => true],
    ];

    public const SHIPMENT_METHOD_UPS = 'shipment_method_UPS';
    public const SHIPMENT_METHOD_DHL = 'shipment_method_DHL';

    private const SHIPMENT_METHODS = [
        self::SHIPMENT_METHOD_UPS,
        self::SHIPMENT_METHOD_DHL,
    ];

    public const SHIPMENT_ZONE_FR = 'shipment_zone_FR';
    public const SHIPMENT_ZONE_EU = 'shipment_zone_EU';
    public const SHIPMENT_ZONE_US = 'shipment_zone_US';

    private const SHIPMENT_ZONES = [
        self::SHIPMENT_ZONE_FR,
        self::SHIPMENT_ZONE_EU,
        self::SHIPMENT_ZONE_US,
    ];

    private const DATA_DIR = __DIR__ . '/../Install/data';

    /**
     * @var StockUnitStateResolverInterface
     */
    private static $stockUnitStateResolver;

    /**
     * @var bool
     */
    private static $taxesLoaded = false;

    /**
     * @var bool
     */
    private static $shippingLoaded = false;

    /**
     * @var array
     */
    private static $ids = [];

    /**
     * @var array
     */
    private static $references = [];


    /**
     * Resolves the stock unit state.
     *
     * @param StockM\StockUnitInterface $stockUnit
     */
    public static function resolveStockUnitState(StockM\StockUnitInterface $stockUnit): void
    {
        if (null === self::$stockUnitStateResolver) {
            self::$stockUnitStateResolver = new StockUnitStateResolver();
        }

        self::$stockUnitStateResolver->resolve($stockUnit);
    }

    /**
     * Creates a tax.
     *
     * Defaults : [
     *     'name'    => null,
     *     'rate'    => null,
     *     'country' => null,
     * ]
     *
     * @param PricingE\Tax|array|int|string $data
     *
     * @return PricingE\Tax
     */
    public static function tax($data = null): PricingE\Tax
    {
        self::loadTaxes();

        /** @var PricingE\Tax $tax */
        [$tax, $return] = self::create($data, PricingE\Tax::class);

        if ($return) {
            return $tax;
        }

        $data = array_replace([
            'code'    => null,
            'name'    => null,
            'rate'    => null,
            'country' => null,
        ], $data);

        if (!(is_numeric($data['rate']) && 0 < $data['rate'] && 100 > $data['rate'])) {
            throw new LogicException("Invalid tax rate : " . $data['rate']);
        }

        $tax
            ->setCode($data['code'])
            ->setName($data['name'])
            ->setRate($data['rate']);

        if (null !== $datum = $data['country']) {
            $tax->setCountry(self::country($datum));
        }

        return $tax;
    }

    /**
     * Creates a tax group.
     *
     * Defaults : [
     *     'name'    => null,
     *     'rate'    => null,
     *     'country' => null,
     * ]
     *
     * @param PricingE\TaxGroup|array|int|string $data
     *
     * @return PricingE\TaxGroup
     */
    public static function taxGroup($data = null): PricingE\TaxGroup
    {
        self::loadTaxes();

        /** @var PricingE\TaxGroup $tax */
        [$group, $return] = self::create($data, PricingE\TaxGroup::class);

        if ($return) {
            return $group;
        }

        $data = array_replace([
            'code'    => null,
            'name'    => null,
            'default' => false,
            'taxes'   => [],
        ], $data);

        $group
            ->setCode($data['code'])
            ->setName($data['name'])
            ->setDefault($data['default']);

        foreach ($data['taxes'] as $tax) {
            $group->addTax(self::tax($tax));
        }

        return $group;
    }

    /**
     * Creates a tax rule.
     *
     * Defaults : [
     *     'code'     => null,
     *     'name'     => null,
     *     'customer' => false,
     *     'business' => false,
     *     'sources'  => [],
     *     'targets'  => [],
     *     'taxes'    => [],
     *     'priority' => 0,
     * ]
     *
     * @param PricingE\TaxRule|array|int|string $data
     *
     * @return PricingE\TaxRule
     */
    public static function taxRule($data = null): PricingE\TaxRule
    {
        self::loadTaxes();

        /** @var PricingE\TaxRule $tax */
        [$rule, $return] = self::create($data, PricingE\TaxRule::class);

        if ($return) {
            return $rule;
        }

        $data = array_replace([
            'code'     => null,
            'name'     => null,
            'customer' => false,
            'business' => false,
            'sources'  => [],
            'targets'  => [],
            'taxes'    => [],
            'priority' => 0,
        ], $data);

        $rule
            ->setCode($data['code'])
            ->setName($data['name'])
            ->setCustomer($data['customer'])
            ->setBusiness($data['business'])
            ->setPriority($data['priority']);

        foreach ($data['sources'] as $country) {
            $rule->addSource(self::country($country));
        }

        foreach ($data['targets'] as $country) {
            $rule->addTarget(self::country($country));
        }

        foreach ($data['taxes'] as $tax) {
            $rule->addTax(self::tax($tax));
        }

        return $rule;
    }

    /**
     * Creates a currency.
     *
     * Defaults : [
     *     'name'    => null,
     *     'code'    => null,
     *     'enabled' => true,
     * ]
     *
     * @param CommonE\Currency|array|int|string $data
     *
     * @return CommonE\Currency
     */
    public static function currency($data = null): CommonE\Currency
    {
        if (null === $data) {
            $data = self::CURRENCY_EUR;
        }
        if (is_string($data) && preg_match('~^[a-zA-Z]{2,3}$~', $data)) {
            $code = strtoupper($data);
            $reference = 'currency_' . $code;
            if (self::has($reference)) {
                /** @noinspection PhpIncompatibleReturnTypeInspection */
                return self::get($reference);
            }
            $data = [
                '_reference' => $reference,
                'code'       => $code,
            ];
        }

        /** @var CommonE\Currency $currency */
        [$currency, $return] = self::create($data, CommonE\Currency::class);

        if ($return) {
            return $currency;
        }

        $data = array_replace([
            'name'    => null,
            'code'    => null,
            'enabled' => true,
        ], $data);

        if (!isset($data['code']) || empty($data['code'])) {
            throw new LogicException("Country code is required.");
        }

        $data['code'] = strtoupper($data['code']);

        if (empty($data['name'])) {
            if (null === $name = Intl::getCurrencyBundle()->getCurrencyName($data['code'])) {
                throw new LogicException("Invalid currency code '{$data['code']}'.");
            }
            $data['name'] = $name;
        }

        $currency
            ->setName($data['name'])
            ->setCode($data['code'])
            ->setEnabled($data['enabled']);

        return $currency;
    }

    /**
     * Creates a context.
     *
     * Defaults: [
     *     'customer_group'   => [],
     *     'invoice_country'  => self::COUNTRY_FR,
     *     'delivery_country' => self::COUNTRY_FR,
     *     'shipping_country' => self::COUNTRY_FR,
     *     'currency'         => self::CURRENCY_EUR,
     *     'locale'           => 'fr',
     *     'vat_display_mode' => VatDisplayModes::MODE_NET,
     *     'business'         => null,
     *     'tax_exempt'       => null,
     *     'date'             => null,
     *     'admin'            => null,
     * ]
     *
     * @param array $data
     *
     * @return ContextInterface
     */
    public static function context(array $data): ContextInterface
    {
        $data = array_replace([
            'customer_group'   => [],
            'invoice_country'  => self::COUNTRY_FR,
            'delivery_country' => self::COUNTRY_FR,
            'shipping_country' => self::COUNTRY_FR,
            'currency'         => self::CURRENCY_EUR,
            'locale'           => 'fr',
            'vat_display_mode' => VatDisplayModes::MODE_NET,
            'business'         => null,
            'tax_exempt'       => null,
            'date'             => null,
            'admin'            => null,
        ], $data);

        $context = new Context();

        if (null !== $datum = $data['customer_group']) {
            $context->setCustomerGroup(self::customerGroup($datum));
        }
        if (null !== $datum = $data['invoice_country']) {
            $context->setInvoiceCountry(self::country($datum));
        }
        if (null !== $datum = $data['delivery_country']) {
            $context->setDeliveryCountry(self::country($datum));
        }
        if (null !== $datum = $data['shipping_country']) {
            $context->setShippingCountry(self::country($datum));
        }
        if (null !== $datum = $data['currency']) {
            $context->setCurrency(self::currency($datum));
        }
        if (null !== $datum = $data['locale']) {
            $context->setLocale($datum);
        }
        if (null !== $datum = $data['vat_display_mode']) {
            $context->setVatDisplayMode($datum);
        }
        if (null !== $datum = $data['business']) {
            $context->setBusiness($datum);
        }
        if (null !== $datum = $data['tax_exempt']) {
            $context->setTaxExempt($datum);
        }
        if (null !== $datum = $data['date']) {
            $context->setDate(self::date($datum));
        }
        if (null !== $datum = $data['admin']) {
            $context->setAdmin($datum);
        }

        return $context;
    }

    /**
     * Creates a country.
     *
     * Defaults : [
     *     'name'    => null,
     *     'code'    => null,
     *     'enabled' => true,
     * ]
     *
     * @param CommonE\Country|array|int|string $data
     *
     * @return CommonE\Country
     */
    public static function country($data = null): CommonE\Country
    {
        if (null === $data) {
            $data = self::COUNTRY_FR;
        }
        if (is_string($data) && preg_match('~^[a-zA-Z]{2,3}$~', $data)) {
            $code = strtoupper($data);
            $reference = 'country_' . $code;
            if (self::has($reference)) {
                /** @noinspection PhpIncompatibleReturnTypeInspection */
                return self::get($reference);
            }
            $data = [
                '_reference' => $reference,
                'code'       => $code,
            ];
        }

        /** @var CommonE\Country $country */
        [$country, $return] = self::create($data, CommonE\Country::class);

        if ($return) {
            return $country;
        }

        $data = array_replace([
            'name'    => null,
            'code'    => null,
            'enabled' => true,
        ], $data);

        if (!isset($data['code']) || empty($data['code'])) {
            throw new LogicException("Country code is required.");
        }

        $data['code'] = strtoupper($data['code']);

        if (empty($data['name'])) {
            if (null === $name = Intl::getRegionBundle()->getCountryName($data['code'])) {
                throw new LogicException("Invalid currency code '{$data['code']}'.");
            }
            $data['name'] = $name;
        }

        $country
            ->setName($data['name'])
            ->setCode($data['code'])
            ->setEnabled($data['enabled']);

        return $country;
    }

    /**
     * Creates a customer.
     *
     * Defaults : [
     *     'group'               => null,
     *     'company'             => 'Acme',
     *     'first_name'          => 'John',
     *     'last_name'           => 'Doe',
     *     'email'               => 'john.doe@acme.org',
     *     'credit_balance'      => 0.,
     *     'outstanding_balance' => 0.,
     * ]
     *
     * @param CustomerE\Customer|array|int|string $data
     *
     * @return CustomerE\Customer
     */
    public static function customer($data = []): CustomerE\Customer
    {
        /** @var CustomerE\Customer $customer */
        [$customer, $return] = self::create($data, CustomerE\Customer::class);

        if ($return) {
            return $customer;
        }

        $data = array_replace([
            'group'               => null,
            'company'             => 'Acme',
            'first_name'          => 'John',
            'last_name'           => 'Doe',
            'email'               => 'john.doe@acme.org',
            'credit_balance'      => 0.,
            'outstanding_balance' => 0.,
        ], $data);

        $customer
            ->setCompany($data['company'])
            ->setFirstName($data['first_name'])
            ->setLastName($data['last_name'])
            ->setEmail($data['email'])
            ->setCreditBalance($data['credit_balance'])
            ->setOutstandingBalance($data['outstanding_balance']);

        if (null !== $datum = $data['group']) {
            $customer->setCustomerGroup(self::customerGroup($datum));
        }

        return $customer;
    }

    /**
     * Creates a customer.
     *
     * Defaults : [
     *     'name'     => 'Unknown',
     *     'default'  => false,
     *     'business' => false,
     * ]
     *
     * @param CustomerE\CustomerGroup|array|int|string $data
     *
     * @return CustomerE\CustomerGroup
     */
    public static function customerGroup($data = []): CustomerE\CustomerGroup
    {
        if (is_string($data) && isset(self::CUSTOMER_GROUPS_MAP[$data]) && !self::has($data)) {
            $data = array_replace(self::CUSTOMER_GROUPS_MAP[$data], [
                '_reference' => $data,
            ]);

            $group = new CustomerE\CustomerGroup();

            self::register($group, $data);
        } else {
            /** @var CustomerE\CustomerGroup $group */
            [$group, $return] = self::create($data, CustomerE\CustomerGroup::class);

            if ($return) {
                return $group;
            }
        }

        $data = array_replace([
            'name'     => 'Unknown',
            'default'  => false,
            'business' => false,
        ], $data);

        $group
            ->setName($data['name'])
            ->setDefault($data['default'])
            ->setBusiness($data['business']);

        return $group;
    }

    /**
     * Creates a new stock unit.
     *
     * Defaults : [
     *     'subject'     => null,
     *     'item'        => null,
     *     'ordered'     => 0.,
     *     'received'    => 0.,
     *     'adjusted'    => 0.,
     *     'sold'        => 0.,
     *     'shipped'     => 0.,
     *     'locked'      => 0.,
     *     'assignments' => [],
     *     'adjustments' => [],
     * ]
     *
     * @param Acme\StockUnit|array|int|string $data
     *
     * @return Acme\StockUnit
     */
    public static function stockUnit($data = []): Acme\StockUnit
    {
        /** @var Acme\StockUnit $unit */
        [$unit, $return] = self::create($data, Acme\StockUnit::class);

        if ($return) {
            return $unit;
        }

        $data = array_replace([
            'subject'        => null,
            'item'           => null,
            'eda'            => null,
            'net_price'      => 0.,
            'shipping_price' => 0.,
            'ordered'        => 0.,
            'received'       => 0.,
            'adjusted'       => 0.,
            'sold'           => 0.,
            'shipped'        => 0.,
            'locked'         => 0.,
            'assignments'    => [],
            'adjustments'    => [],
        ], $data);

        $subject = null;
        if (null !== $datum = $data['subject']) {
            $subject = self::subject($datum);
        }

        $item = null;
        if (null !== $datum = $data['item']) {
            $unit->setSupplierOrderItem($item = self::supplierOrderItem($datum));

            if ($item->getSubjectIdentity()->hasIdentity()) {
                $s = $item->getSubjectIdentity()->getSubject();
                if ($subject) {
                    if ($s !== $subject) {
                        throw new LogicException("Subject miss match");
                    }
                } else {
                    $subject = $s;
                }
            } elseif ($subject) {
                self::assignSubject($item, $subject);
            }
        }

        if ($subject) {
            $unit->setSubject($subject);
        }

        $unit
            ->setNetPrice($data['net_price'])
            ->setShippingPrice($data['shipping_price'])
            ->setOrderedQuantity($data['ordered'])
            ->setReceivedQuantity($data['received'])
            ->setAdjustedQuantity($data['adjusted'])
            ->setSoldQuantity($data['sold'])
            ->setShippedQuantity($data['shipped'])
            ->setLockedQuantity($data['locked']);

        if (null !== $datum = $data['eda']) {
            $unit->setEstimatedDateOfArrival(self::date($datum));
        }

        foreach ($data['assignments'] as $datum) {
            $unit->addStockAssignment(self::stockAssignment($datum));
        }

        foreach ($data['adjustments'] as $datum) {
            $unit->addStockAdjustment(self::stockAdjustment($datum));
        }

        self::resolveStockUnitState($unit);

        return $unit;
    }

    /**
     * Creates a stock assignment.
     *
     * Defaults : [
     *     'unit'    => null,
     *     'item'    => null,
     *     'sold'    => 0.,
     *     'shipped' => 0.,
     *     'locked'  => 0.,
     * ]
     *
     * @param OrderE\OrderItemStockAssignment|array|int|string $data
     *
     * @return OrderE\OrderItemStockAssignment
     */
    public static function stockAssignment($data = []): OrderE\OrderItemStockAssignment
    {
        /** @var OrderE\OrderItemStockAssignment $assignment */
        [$assignment, $return] = self::create($data, OrderE\OrderItemStockAssignment::class);

        if ($return) {
            return $assignment;
        }

        $data = array_replace([
            'unit'    => null,
            'item'    => null,
            'sold'    => 0.,
            'shipped' => 0.,
            'locked'  => 0.,
        ], $data);

        if (null !== $datum = $data['unit']) {
            $assignment->setStockUnit(self::stockUnit($datum));
        }

        if (null !== $datum = $data['item']) {
            $assignment->setOrderItem(self::orderItem($datum));
        }

        $assignment
            ->setSoldQuantity($data['sold'])
            ->setShippedQuantity($data['shipped'])
            ->setLockedQuantity($data['locked']);

        return $assignment;
    }

    /**
     * Creates a new stock adjustment.
     *
     * Defaults :[
     *     'unit'     => null,
     *     'quantity' => 1.,
     *     'debit'    => false,
     * ]
     *
     * @param StockE\StockAdjustment|array|int|string $data
     *
     * @return StockE\StockAdjustment
     */
    public static function stockAdjustment($data = []): StockE\StockAdjustment
    {
        /** @var StockE\StockAdjustment $adjustment */
        [$adjustment, $return] = self::create($data, StockE\StockAdjustment::class);

        if ($return) {
            return $adjustment;
        }

        $data = array_replace([
            'unit'     => null,
            'quantity' => 1.,
            'debit'    => false,
        ], $data);

        if (null !== $datum = $data['unit']) {
            $adjustment->setStockUnit(self::stockUnit($datum));
        }

        $adjustment
            ->setQuantity($data['quantity'])
            ->setReason($data['debit']
                ? StockM\StockAdjustmentReasons::REASON_DEBIT
                : StockM\StockAdjustmentReasons::REASON_CREDIT
            );

        return $adjustment;
    }

    /**
     * Creates a new subject (acme product).
     *
     * Defaults : [
     *     'designation' => 'Apple iPhone',
     *     'reference'   => 'APPL-IPHO',
     *     'price'       => 249.,
     *     'weight'      => .8,
     * ]
     *
     * @param Acme\Product|array|int|string $data
     *
     * @return Acme\Product
     */
    public static function subject($data = []): Acme\Product
    {
        /** @var Acme\Product $subject */
        [$subject, $return] = self::create($data, Acme\Product::class);

        if ($return) {
            return $subject;
        }

        $data = array_replace([
            'designation' => 'Subject Test',
            'reference'   => 'SU-TE',
            'price'       => 49.99,
            'weight'      => .8,
            'tax_group'   => self::TAX_GROUP_NORMAL,
        ], $data);

        $subject
            ->setDesignation($data['designation'])
            ->setReference($data['reference'])
            ->setNetPrice($data['price'])
            ->setWeight($data['weight']);

        if (null !== $datum = $data['tax_group']) {
            $subject->setTaxGroup(self::taxGroup($datum));
        }

        return $subject;
    }

    /**
     * Creates a new supplier.
     *
     * Defaults : [
     *     'name'     => 'Foo supply',
     *     'currency' => self::CURRENCY_EUR,
     *     'tax'      => null,
     *     'carrier'  => null,
     * ]
     *
     * @param SupplierE\Supplier|array|int|string $data
     *
     * @return SupplierE\Supplier
     */
    public static function supplier($data = []): SupplierE\Supplier
    {
        /** @var SupplierE\Supplier $subject */
        [$subject, $return] = self::create($data, SupplierE\Supplier::class);

        if ($return) {
            return $subject;
        }

        $data = array_replace([
            'name'     => 'Foo supplier',
            'currency' => self::CURRENCY_EUR,
            'tax'      => null,
            'carrier'  => null,
            'address'  => null,
        ], $data);

        $subject
            ->setName($data['name'])
            ->setCurrency(self::currency($data['currency']));

        if (null !== $datum = $data['tax']) {
            $subject->setTax(self::tax($datum));
        }

        if (null !== $datum = $data['carrier']) {
            $subject->setCarrier(self::supplierCarrier($datum));
        }

        if (null !== $datum = $data['address']) {
            $subject->setAddress(self::supplierAddress($datum));
        }

        return $subject;
    }

    /**
     * Creates a new supplier carrier.
     *
     * Defaults : [
     *     'name' => 'Foo carrier',
     *     'tax'  => null,
     * ]
     *
     * @param SupplierE\SupplierCarrier|array|int|string $data
     *
     * @return SupplierE\SupplierCarrier
     */
    public static function supplierCarrier($data = []): SupplierE\SupplierCarrier
    {
        /** @var SupplierE\SupplierCarrier $carrier */
        [$carrier, $return] = self::create($data, SupplierE\SupplierCarrier::class);

        if ($return) {
            return $carrier;
        }

        $data = array_replace([
            'name' => 'Foo carrier',
            'tax'  => null,
        ], $data);

        $carrier->setName($data['name']);

        if (null !== $datum = $data['tax']) {
            $carrier->setTax(self::tax($datum));
        }

        return $carrier;
    }

    /**
     * Creates a new supplier address.
     *
     * @param SupplierE\SupplierAddress|array|int|string $data
     *
     * @return SupplierE\SupplierAddress
     *
     * @see Fixture::fillAddress()
     */
    public static function supplierAddress($data = []): SupplierE\SupplierAddress
    {
        /** @var SupplierE\SupplierAddress $address */
        [$address, $return] = self::create($data, SupplierE\SupplierAddress::class);

        if ($return) {
            return $address;
        }

        self::fillAddress($address, $data);

        return $address;
    }

    /**
     * Creates a new supplier product.
     *
     * Defaults : [
     *     'supplier'    => null,
     *     'subject'     => null,
     *     'tax_group'   => self::TAX_GROUP_NORMAL,
     *     'designation' => 'Apple iPhone',
     *     'reference'   => 'APPL-IPHO',
     *     'price'       => 190.,
     *     'weight'      => .8,
     *     'available'   => 0.,
     *     'ordered'     => 0.,
     *     'eda'         => null,
     * ]
     *
     * @param SupplierE\SupplierProduct|array|int|string $data
     *
     * @return SupplierE\SupplierProduct
     */
    public static function supplierProduct($data = []): SupplierE\SupplierProduct
    {
        /** @var SupplierE\SupplierProduct $product */
        [$product, $return] = self::create($data, SupplierE\SupplierProduct::class);

        if ($return) {
            return $product;
        }

        $data = array_replace([
            'supplier'    => null,
            'subject'     => null,
            'tax_group'   => self::TAX_GROUP_NORMAL,
            'designation' => '',
            'reference'   => '',
            'price'       => 0.,
            'weight'      => 0.,
            'available'   => 0.,
            'ordered'     => 0.,
            'eda'         => null,
        ], $data);

        $product
            ->setDesignation($data['designation'])
            ->setReference($data['reference'])
            ->setNetPrice($data['price'])
            ->setWeight($data['weight'])
            ->setAvailableStock($data['available'])
            ->setOrderedStock($data['ordered']);

        if (null !== $datum = $data['supplier']) {
            $product->setSupplier(self::supplier($datum));
        }

        if (null !== $datum = $data['tax_group']) {
            $product->setTaxGroup(self::taxGroup($datum));
        }

        if (null !== $datum = $data['eda']) {
            $product->setEstimatedDateOfArrival(self::date($datum));
        }

        if (null !== $datum = $data['subject']) {
            self::assignSubject($product, $datum);
        }

        return $product;
    }

    /**
     * Creates a new supplier order.
     *
     * Defaults : [
     *     'currency'        => self::CURRENCY_EUR,
     *     'shipping_cost'   => .0,
     *     'discount_total'  => .0,
     *     'tax_total'       => .0,
     *     'payment_total'   => .0,
     *     'customs_tax'     => .0,
     *     'customs_vat'     => .0,
     *     'forwarder_fee'   => .0,
     *     'forwarder_total' => .0,
     *     'created_at'      => 'now',
     *     'ordered_at'      => null,
     *     'supplier'        => null,
     *     'carrier'         => null,
     *     'warehouse'       => null,
     *     'items'           => [],
     * ]
     *
     * @param SupplierE\SupplierOrder|array|int|string $data
     *
     * @return SupplierE\SupplierOrder
     */
    public static function supplierOrder($data = []): SupplierE\SupplierOrder
    {
        /** @var SupplierE\SupplierOrder $order */
        [$order, $return] = self::create($data, SupplierE\SupplierOrder::class);

        if ($return) {
            return $order;
        }

        $data = array_replace([
            'currency'        => self::CURRENCY_EUR,
            'shipping_cost'   => .0,
            'discount_total'  => .0,
            'tax_total'       => .0,
            'payment_total'   => .0,
            'customs_tax'     => .0,
            'customs_vat'     => .0,
            'forwarder_fee'   => .0,
            'forwarder_total' => .0,
            'exchange_rate'   => null,
            'exchange_date'   => null,
            'created_at'      => 'now',
            'ordered_at'      => null,
            'supplier'        => null,
            'carrier'         => null,
            'warehouse'       => null,
            'items'           => [],
        ], $data);

        $order
            ->setCurrency(self::currency($data['currency']))
            ->setShippingCost($data['shipping_cost'])
            ->setDiscountTotal($data['discount_total'])
            ->setTaxTotal($data['tax_total'])
            ->setPaymentTotal($data['payment_total'])
            ->setCustomsTax($data['customs_tax'])
            ->setCustomsVat($data['customs_vat'])
            ->setForwarderFee($data['forwarder_fee'])
            ->setForwarderTotal($data['forwarder_total'])
            ->setExchangeRate($data['exchange_rate']);

        if (null !== $datum = $data['exchange_date']) {
            $order->setExchangeDate(self::date($datum));
        }

        if (null !== $datum = $data['created_at']) {
            $order->setOrderedAt(self::date($datum));
        }

        if (null !== $datum = $data['ordered_at']) {
            $order->setOrderedAt(self::date($datum));
        }

        if (null !== $data['supplier']) {
            $order->setSupplier(self::supplier($data['supplier']));
        }

        if (null !== $data['carrier']) {
            $order->setCarrier(self::supplierCarrier($data['carrier']));
        }

        if (null !== $data['warehouse']) {
            $order->setWarehouse(self::warehouse($data['warehouse']));
        }

        foreach ($data['items'] as $datum) {
            $order->addItem(self::supplierOrderItem($datum));
        }

        return $order;
    }

    /**
     * Creates a new supplier order item.
     *
     * Defaults : [
     *     'order'     => null,
     *     'product'   => null,
     *     'subject'   => null,
     *     'unit'      => null,
     *     'tax_group' => null,
     *     'designation' => '',
     *     'reference'   => '',
     *     'price'       => 0.,
     *     'weight'      => 0.,
     *     'quantity'  => 1.,
     * ]
     *
     * @param SupplierE\SupplierOrderItem|array|int|string $data
     *
     * @return SupplierE\SupplierOrderItem
     */
    public static function supplierOrderItem($data = []): SupplierE\SupplierOrderItem
    {
        /** @var SupplierE\SupplierOrderItem $item */
        [$item, $return] = self::create($data, SupplierE\SupplierOrderItem::class);

        if ($return) {
            return $item;
        }

        $data = array_replace([
            'order'       => null,
            'product'     => null,
            'subject'     => null,
            'unit'        => null,
            'tax_group'   => null,
            'designation' => '',
            'reference'   => '',
            'price'       => 0.,
            'weight'      => 0.,
            'quantity'    => 1.,
        ], $data);

        if (null !== $datum = $data['order']) {
            $item->setOrder(self::supplierOrder($datum));
        }

        $unit = null;
        if (null !== $datum = $data['unit']) {
            $item->setStockUnit($unit = self::stockUnit($datum));
        }

        $product = null;
        if (null !== $datum = $data['product']) {
            $item->setProduct($product = self::supplierProduct($datum));
        }

        if (null !== $datum = $data['tax_group']) {
            $item->setTaxGroup(self::taxGroup($datum));
        }

        $subject = null;
        if (null !== $datum = $data['subject']) {
            $subject = self::subject($datum);
        }
        if ($product && $s = $product->getSubjectIdentity()->getSubject()) {
            if (!$subject) {
                $subject = $s;
            } elseif ($subject !== $s) {
                throw new LogicException("Subject miss match");
            }
        }
        if ($unit && $s = $unit->getProduct()) {
            if (!$subject) {
                $subject = $s;
            } elseif ($subject !== $s) {
                throw new LogicException("Subject miss match");
            }
        }

        $item
            ->setDesignation($data['designation'])
            ->setReference($data['reference'])
            ->setNetPrice($data['price'])
            ->setWeight($data['weight'])
            ->setQuantity($data['quantity']);

        if ($subject) {
            self::assignSubject($item, $subject);
        }

        return $item;
    }

    /**
     * Creates a payment.
     *
     * Defaults : [
     *     'order'         => null,
     *     'method'        => null,
     *     'state'         => PaymentStates::STATE_CAPTURED,
     *     'currency'      => self::CURRENCY_EUR,
     *     'amount'        => 0.,
     *     'exchange_rate' => 1.,
     *     'exchange_date' => 'now',
     * ]
     *
     * @param OrderE\OrderPayment|array|int|string $data
     *
     * @return OrderE\OrderPayment
     */
    public static function payment($data = []): OrderE\OrderPayment
    {
        /** @var OrderE\OrderPayment $payment */
        [$payment, $return] = self::create($data, OrderE\OrderPayment::class);

        if ($return) {
            return $payment;
        }

        $data = array_replace([
            'order'         => null,
            'method'        => self::PAYMENT_METHOD_DEFAULT,
            'refund'        => false,
            'state'         => PaymentStates::STATE_CAPTURED,
            'currency'      => self::CURRENCY_EUR,
            'amount'        => 0.,
            'exchange_rate' => 1.,
            'exchange_date' => 'now',
        ], $data);

        if (null !== $data['order']) {
            $payment->setOrder(self::order($data['order']));
        }

        if (null !== $data['method']) {
            $payment->setMethod(self::paymentMethod($data['method']));
        }

        $payment
            ->setRefund($data['refund'])
            ->setState($data['state'])
            ->setCurrency(self::currency($data['currency']))
            ->setAmount($data['amount'])
            ->setRealAmount($data['amount'])
            ->setExchangeRate($data['exchange_rate']);

        if (null !== $datum = $data['exchange_date']) {
            $payment->setExchangeDate(self::date($datum));
        }

        return $payment;
    }

    /**
     * Creates a payment.
     *
     * Defaults : [
     *     'manual'      => false,
     *     'outstanding' => false,
     *     'credit'      => false,
     * ]
     *
     * @param Acme\PaymentMethod|array|int|string $data
     *
     * @return Acme\PaymentMethod
     */
    public static function paymentMethod($data = []): Acme\PaymentMethod
    {
        if (is_string($data) && isset(self::PAYMENT_METHODS_MAP[$data]) && !self::has($data)) {
            $data = array_replace(self::PAYMENT_METHODS_MAP[$data], [
                '_reference' => $data,
            ]);

            $method = new Acme\PaymentMethod();

            self::register($method, $data);
        } else {
            /** @var Acme\PaymentMethod $method */
            [$method, $return] = self::create($data, Acme\PaymentMethod::class);

            if ($return) {
                return $method;
            }
        }

        $data = array_replace([
            'enabled'     => true,
            'available'   => true,
            'manual'      => false,
            'outstanding' => false,
            'credit'      => false,
        ], $data);

        $method
            ->setEnabled($data['enabled'])
            ->setAvailable($data['available'])
            ->setManual($data['manual'])
            ->setCredit($data['credit'])
            ->setOutstanding($data['outstanding']);

        return $method;
    }

    /**
     * Creates a new order.
     *
     * Defaults : [
     *     'currency'             => self::CURRENCY_EUR,
     *     'state'                => OrderM\OrderStates::STATE_NEW,
     *     'customer'             => null,
     *     'invoice_address'      => null,
     *     'delivery_address'     => null,
     *     'weight_total'         => 0.,
     *     'shipment_weight'      => 0.,
     *     'shipment_amount'      => 0.,
     *     'grand_total'          => 0.,
     *     'deposit_total'        => 0.,
     *     'pending_total'        => 0.,
     *     'paid_total'           => 0.,
     *     'outstanding_accepted' => 0.,
     *     'outstanding_expired'  => 0.,
     *     'exchange_rate'        => null,
     *     'exchange_date'        => null,
     *     'invoice_total'        => 0.,
     *     'credit_total'         => 0.,
     *     'created_at'           => 'now',
     *     'items'                => [],
     *     'discounts'            => [],
     *     'taxes'                => [],
     *     'payments'             => [],
     *     'shipments'            => [],
     *     'invoices'             => [],
     * ]
     *
     * @param OrderE\Order|array|int|string $data
     *
     * @return OrderE\Order
     */
    public static function order($data = []): OrderE\Order
    {
        /** @var OrderE\Order $order */
        [$order, $return] = self::create($data, OrderE\Order::class);

        if ($return) {
            return $order;
        }

        $data = array_replace([
            'currency'             => self::CURRENCY_EUR,
            'state'                => OrderM\OrderStates::STATE_NEW,
            'customer'             => null,
            'customer_group'       => null,
            'vat_valid'            => false,
            'vat_number'           => null,
            'invoice_address'      => null,
            'delivery_address'     => null,
            'same_address'         => true,
            'shipment_method'      => null,
            'shipment_weight'      => null,
            'shipment_amount'      => 0.,
            'weight_total'         => 0.,
            'grand_total'          => 0.,
            'deposit_total'        => 0.,
            'pending_total'        => 0.,
            'paid_total'           => 0.,
            'outstanding_accepted' => 0.,
            'outstanding_expired'  => 0.,
            'exchange_rate'        => null,
            'exchange_date'        => null,
            'invoice_total'        => 0.,
            'credit_total'         => 0.,
            'created_at'           => 'now',
            'items'                => [],
            'discounts'            => [],
            'taxes'                => [],
            'payments'             => [],
            'shipments'            => [],
            'invoices'             => [],
        ], $data);

        $order
            ->setCurrency(self::currency($data['currency']))
            ->setState($data['state'])
            ->setVatValid($data['vat_valid'])
            ->setVatNumber($data['vat_number'])
            ->setWeightTotal($data['weight_total'])
            ->setShipmentWeight($data['shipment_weight'])
            ->setShipmentAmount($data['shipment_amount'])
            ->setGrandTotal($data['grand_total'])
            ->setDepositTotal($data['deposit_total'])
            ->setPendingTotal($data['pending_total'])
            ->setPaidTotal($data['paid_total'])
            ->setOutstandingAccepted($data['outstanding_accepted'])
            ->setOutstandingExpired($data['outstanding_expired'])
            ->setExchangeRate($data['exchange_rate'])
            ->setInvoiceTotal($data['invoice_total'])
            ->setCreditTotal($data['credit_total']);

        if (null !== $datum = $data['shipment_method']) {
            $order->setShipmentMethod(self::shipmentMethod($datum));
        }

        if (null !== $datum = $data['exchange_date']) {
            $order->setExchangeDate(self::date($datum));
        }

        if (null !== $datum = $data['created_at']) {
            $order->setCreatedAt(self::date($datum));
        }

        if (null !== $datum = $data['customer']) {
            $order->setCustomer(self::customer($datum));
        }

        if (null !== $datum = $data['customer_group']) {
            $order->setCustomerGroup(self::customerGroup($datum));
        }

        if (null !== $datum = $data['invoice_address']) {
            $order->setInvoiceAddress(self::orderAddress($datum));
        }

        if (null !== $datum = $data['delivery_address']) {
            $order->setDeliveryAddress(self::orderAddress($datum));
            $data['same_address'] = false;
        }

        $order->setSameAddress($data['same_address']);

        foreach ($data['items'] as $datum) {
            $order->addItem(self::orderItem($datum));
        }

        foreach ($data['discounts'] as $datum) {
            if (is_numeric($datum)) {
                $datum = [
                    'type'   => CommonM\AdjustmentTypes::TYPE_DISCOUNT,
                    'amount' => $datum,
                ];
            }

            $order->addAdjustment(self::orderAdjustment($datum));
        }

        foreach ($data['taxes'] as $datum) {
            if (is_numeric($datum)) {
                $datum = [
                    'type'   => CommonM\AdjustmentTypes::TYPE_TAXATION,
                    'amount' => $datum,
                ];
            }

            $order->addAdjustment(self::orderAdjustment($datum));
        }

        foreach ($data['payments'] as $datum) {
            $order->addPayment(self::payment($datum));
        }

        foreach ($data['shipments'] as $datum) {
            $order->addShipment(self::shipment($datum));
        }

        foreach ($data['invoices'] as $datum) {
            $order->addInvoice(self::invoice($datum));
        }

        return $order;
    }

    /**
     * Creates a new order address.
     *
     * @param OrderE\OrderAddress|array|int|string $data
     *
     * @return OrderE\OrderAddress
     *
     * @see Fixture::fillAddress()
     */
    public static function orderAddress($data = []): OrderE\OrderAddress
    {
        /** @var OrderE\OrderAddress $address */
        [$address, $return] = self::create($data, OrderE\OrderAddress::class);

        if ($return) {
            return $address;
        }

        self::fillAddress($address, $data);

        return $address;
    }

    /**
     * Creates a new order item.
     *
     * Defaults : [
     *     'order'       => null,
     *     'parent'      => null,
     *     'subject'     => null,
     *     'tax_group'   => null,
     *     'designation' => '',
     *     'reference'   => '',
     *     'price'       => 0.,
     *     'weight'      => 0.,
     *     'quantity'    => 1.,
     *     'private'     => false,
     *     'compound'    => false,
     *     'immutable'   => false,
     *     'discounts'   => [],
     *     'taxes'       => [],
     *     'children'    => [],
     *     'assignments' => [],
     * ]
     *
     * @param OrderE\OrderItem|array|int|string $data
     *
     * @return OrderE\OrderItem
     */
    public static function orderItem($data = []): OrderE\OrderItem
    {
        /** @var OrderE\OrderItem $item */
        [$item, $return] = self::create($data, OrderE\OrderItem::class);

        if ($return) {
            return $item;
        }

        $data = array_replace([
            'order'       => null,
            'parent'      => null,
            'subject'     => null,
            'tax_group'   => null,
            'designation' => '',
            'reference'   => '',
            'price'       => 0.,
            'weight'      => 0.,
            'quantity'    => 1.,
            'private'     => false,
            'compound'    => false,
            'immutable'   => false,
            'discounts'   => [],
            'taxes'       => [],
            'children'    => [],
            'assignments' => [],
        ], $data);

        $item
            ->setDesignation($data['designation'])
            ->setReference($data['reference'])
            ->setNetPrice($data['price'])
            ->setWeight($data['weight'])
            ->setQuantity($data['quantity'])
            ->setPrivate($data['private'])
            ->setCompound($data['compound'])
            ->setImmutable($data['immutable']);

        if (null !== $datum = $data['order']) {
            $item->setOrder(self::order($datum));
        }

        if (null !== $datum = $data['parent']) {
            $item->setParent(self::orderItem($datum));
        }

        if (null !== $datum = $data['tax_group']) {
            $item->setTaxGroup(self::taxGroup($datum));
        }

        if (null !== $datum = $data['subject']) {
            self::assignSubject($item, $datum);
        }

        foreach ($data['discounts'] as $datum) {
            if (is_numeric($datum)) {
                $datum = [
                    'type'   => CommonM\AdjustmentTypes::TYPE_DISCOUNT,
                    'amount' => $datum,
                ];
            }

            $item->addAdjustment(self::orderItemAdjustment($datum));
        }

        foreach ($data['taxes'] as $datum) {
            if (is_numeric($datum)) {
                $datum = [
                    'type'   => CommonM\AdjustmentTypes::TYPE_TAXATION,
                    'amount' => $datum,
                ];
            }

            $item->addAdjustment(self::orderItemAdjustment($datum));
        }

        foreach ($data['children'] as $datum) {
            $item->addChild(self::orderItem($datum));
        }

        foreach ($data['assignments'] as $datum) {
            $item->addStockAssignment(self::stockAssignment($datum));
        }

        return $item;
    }

    /**
     * Creates a new order item adjustment.
     *
     * @param OrderE\OrderItemAdjustment|array|int|string $data
     *
     * @return OrderE\OrderItemAdjustment
     */
    public static function orderItemAdjustment($data = []): OrderE\OrderItemAdjustment
    {
        /** @var OrderE\OrderItemAdjustment $adjustment */
        [$adjustment, $return] = self::create($data, OrderE\OrderItemAdjustment::class);

        if ($return) {
            return $adjustment;
        }

        $data = array_replace([
            'type'   => CommonM\AdjustmentTypes::TYPE_TAXATION,
            'mode'   => CommonM\AdjustmentModes::MODE_PERCENT,
            'item'   => null,
            'amount' => null,
            'source' => null,
        ], $data);

        if (null !== $datum = $data['item']) {
            $adjustment->setItem(self::orderItem($datum));
        }

        $adjustment
            ->setType($data['type'])
            ->setMode($data['mode'])
            ->setDesignation(self::adjustmentDesignation($data))
            ->setAmount($data['amount'])
            ->setSource($data['source']);

        return $adjustment;
    }

    /**
     * Creates a new order adjustment.
     *
     * @param OrderE\OrderAdjustment|array|int|string $data
     *
     * @return OrderE\OrderAdjustment
     */
    public static function orderAdjustment($data = []): OrderE\OrderAdjustment
    {
        /** @var OrderE\OrderAdjustment $adjustment */
        [$adjustment, $return] = self::create($data, OrderE\OrderAdjustment::class);

        if ($return) {
            return $adjustment;
        }

        $data = array_replace([
            'type'   => CommonM\AdjustmentTypes::TYPE_TAXATION,
            'mode'   => CommonM\AdjustmentModes::MODE_PERCENT,
            'order'  => null,
            'amount' => null,
            'source' => null,
        ], $data);

        if (null !== $datum = $data['order']) {
            $adjustment->setOrder(self::order($datum));
        }

        $adjustment
            ->setType($data['type'])
            ->setMode($data['mode'])
            ->setDesignation(self::adjustmentDesignation($data))
            ->setAmount($data['amount'])
            ->setSource($data['source']);

        return $adjustment;
    }

    /**
     * Creates a new order taxation adjustment (for shipment).
     *
     * @param float $amount
     *
     * @return OrderE\OrderAdjustment
     *
     * @deprecated Use Fixture::orderAdjustment
     * @TODO       Remove
     */
    public static function orderTaxationAdjustment(float $amount): OrderE\OrderAdjustment
    {
        $adjustment = new OrderE\OrderAdjustment();
        $adjustment
            ->setType(CommonM\AdjustmentTypes::TYPE_TAXATION)
            ->setMode(CommonM\AdjustmentModes::MODE_PERCENT)
            ->setDesignation("VAT $amount%")
            ->setAmount($amount);

        return $adjustment;
    }

    /**
     * Creates a new order discount adjustment.
     *
     * @param float $amount
     * @param bool  $flat
     *
     * @return OrderE\OrderAdjustment
     *
     * @deprecated Use Fixture::orderAdjustment
     * @TODO       Remove
     */
    public static function orderDiscountAdjustment(float $amount, bool $flat = false): OrderE\OrderAdjustment
    {
        $adjustment = new OrderE\OrderAdjustment();
        $adjustment
            ->setType(CommonM\AdjustmentTypes::TYPE_DISCOUNT)
            ->setMode($flat ? CommonM\AdjustmentModes::MODE_FLAT : CommonM\AdjustmentModes::MODE_PERCENT)
            ->setDesignation($flat ? "Discount" : "Discount $amount%")
            ->setAmount($amount);

        return $adjustment;
    }

    /**
     * Creates a new shipment.
     *
     * Defaults : [
     *     'order' => null,
     *     'items' => [],
     * ]
     *
     * @param OrderE\OrderShipment|array|int|string $data
     *
     * @return OrderE\OrderShipment
     */
    public static function shipment($data = []): OrderE\OrderShipment
    {
        /** @var OrderE\OrderShipment $shipment */
        [$shipment, $return] = self::create($data, OrderE\OrderShipment::class);

        if ($return) {
            return $shipment;
        }

        $data = array_replace([
            'order'  => null,
            'method' => null,
            'return' => false,
            'items'  => [],
        ], $data);

        if (null !== $datum = $data['order']) {
            $shipment->setOrder(self::order($datum));
        }

        if (null !== $datum = $data['method']) {
            $shipment->setMethod(self::shipmentMethod($datum));
        }

        foreach ($data['items'] as $item) {
            $shipment->addItem(self::shipmentItem($item));
        }

        return $shipment;
    }

    /**
     * Creates a new shipment address.
     *
     * @param array $data
     *
     * @return ShipmentAddress
     *
     * @see Fixture::fillAddress()
     */
    public static function shipmentAddress(array $data = []): ShipmentAddress
    {
        $address = new ShipmentAddress();

        self::fillAddress($address, $data);

        return $address;
    }

    /**
     * Creates a new shipment item.
     *
     * Defaults : [
     *     'shipment' => null,
     *     'item'     => null,
     *     'quantity' => 1.,
     * ]
     *
     * @param OrderE\OrderShipmentItem|array|int|string $data
     *
     * @return OrderE\OrderShipmentItem
     */
    public static function shipmentItem($data = []): OrderE\OrderShipmentItem
    {
        /** @var OrderE\OrderShipmentItem $item */
        [$item, $return] = self::create($data, OrderE\OrderShipmentItem::class);

        if ($return) {
            return $item;
        }

        $data = array_replace([
            'shipment' => null,
            'item'     => null,
            'quantity' => 1.,
        ], $data);

        if (null !== $datum = $data['shipment']) {
            $item->setShipment(self::shipment($datum));
        }

        if (null !== $datum = $data['item']) {
            $item->setOrderItem(self::orderItem($datum));
        }

        $item->setQuantity($data['quantity']);

        return $item;
    }

    /**
     * Creates a payment.
     *
     * Defaults : [
     *     'manual'      => false,
     *     'outstanding' => false,
     *     'credit'      => false,
     * ]
     *
     * @param ShipmentE\ShipmentMethod|array|int|string $data
     *
     * @return ShipmentE\ShipmentMethod
     */
    public static function shipmentMethod($data = []): ShipmentE\ShipmentMethod
    {
        self::loadShipping();

        /** @var ShipmentE\ShipmentMethod $method */
        [$method, $return] = self::create($data, ShipmentE\ShipmentMethod::class);

        if ($return) {
            return $method;
        }

        $data = array_replace([
            'tax_group' => self::TAX_GROUP_NORMAL,
            'name'      => 'Foo',
            'enabled'   => true,
            'available' => true,
            'platform'  => 'foo',
            'gateway'   => 'foo',
            'prices'    => [],
        ], $data);

        $method
            ->setName($data['name'])
            ->setEnabled($data['enabled'])
            ->setAvailable($data['available'])
            ->setPlatformName($data['platform'])
            ->setGatewayName($data['gateway']);

        if (null !== $datum = $data['tax_group']) {
            $method->setTaxGroup(self::taxGroup($datum));
        }

        foreach ($data['prices'] as $datum) {
            $method->addPrice(self::shipmentPrice($datum));
        }

        return $method;
    }

    /**
     * Creates a new shipment price.
     *
     * Defaults : [
     *     'method' => null,
     *     'zone'   => null,
     *     'weight' => 0.,
     *     'price'  => 0.,
     * ]
     *
     * @param ShipmentE\ShipmentPrice|array|int|string $data
     *
     * @return ShipmentE\ShipmentPrice
     */
    public static function shipmentPrice($data = []): ShipmentE\ShipmentPrice
    {
        self::loadShipping();

        /** @var ShipmentE\ShipmentPrice $price */
        [$price, $return] = self::create($data, ShipmentE\ShipmentPrice::class);

        if ($return) {
            return $price;
        }

        $data = array_replace([
            'method' => null,
            'zone'   => null,
            'weight' => 0.,
            'price'  => 0.,
        ], $data);

        $price
            ->setWeight($data['weight'])
            ->setNetPrice($data['price']);

        if (null !== $datum = $data['method']) {
            $price->setMethod(self::shipmentMethod($datum));
        }

        if (null !== $datum = $data['zone']) {
            $price->setZone(self::shipmentZone($datum));
        }

        return $price;
    }

    /**
     * Creates a new shipment rule.
     *
     * Defaults : [
     *     'method' => null,
     *     'zone'   => null,
     *     'weight' => 0.,
     *     'rule'  => 0.,
     * ]
     *
     * @param ShipmentE\ShipmentRule|array|int|string $data
     *
     * @return ShipmentE\ShipmentRule
     */
    public static function shipmentRule($data = []): ShipmentE\ShipmentRule
    {
        self::loadShipping();

        /** @var ShipmentE\ShipmentRule $rule */
        [$rule, $return] = self::create($data, ShipmentE\ShipmentRule::class);

        if ($return) {
            return $rule;
        }

        $data = array_replace([
            'name'            => 'Shipment rule',
            'methods'         => [],
            'countries'       => [],
            'customer_groups' => [],
            'base_total'      => 0.,
            'vat_mode'        => VatDisplayModes::MODE_NET,
            'start_at'        => null,
            'end_at'          => null,
            'price'           => 0.,
        ], $data);

        $rule
            ->setName($data['name'])
            ->setBaseTotal($data['base_total'])
            ->setVatMode($data['vat_mode'])
            ->setStartAt(self::date($data['start_at']))
            ->setEndAt(self::date($data['end_at']))
            ->setNetPrice($data['price']);

        foreach ($data['methods'] as $datum) {
            $rule->addMethod(self::shipmentMethod($datum));
        }

        foreach ($data['countries'] as $datum) {
            $rule->addCountry(self::country($datum));
        }

        foreach ($data['customer_groups'] as $datum) {
            $rule->addCustomerGroup(self::customerGroup($datum));
        }

        return $rule;
    }

    /**
     * Creates a new shipment zone.
     *
     * Defaults : [
     *     'name'      => 'foo',
     *     'countries' => [],
     *     'prices'    => [],
     * ]
     *
     * @param ShipmentE\ShipmentZone|array|int|string $data
     *
     * @return ShipmentE\ShipmentZone
     */
    public static function shipmentZone($data = []): ShipmentE\ShipmentZone
    {
        self::loadShipping();

        /** @var ShipmentE\ShipmentZone $zone */
        [$zone, $return] = self::create($data, ShipmentE\ShipmentZone::class);

        if ($return) {
            return $zone;
        }

        $data = array_replace([
            'name'      => 'foo',
            'countries' => [],
            'prices'    => [],
        ], $data);

        $zone->setName($data['name']);

        foreach ($data['countries'] as $datum) {
            $zone->addCountry(self::country($datum));
        }

        foreach ($data['prices'] as $datum) {
            $zone->addPrice(self::shipmentPrice($datum));
        }

        return $zone;
    }

    /**
     * Creates a new invoice.
     *
     * Defaults : [
     *     'order'       => null,
     *     'credit'      => false,
     *     'currency'    => self::CURRENCY_EUR
     *     'grand_total' => 0.,
     *     'lines'       => [],
     * ]
     *
     * @param OrderE\OrderInvoice|array|int|string $data
     *
     * @return OrderE\OrderInvoice
     */
    public static function invoice($data = []): OrderE\OrderInvoice
    {
        /** @var OrderE\OrderInvoice $invoice */
        [$invoice, $return] = self::create($data, OrderE\OrderInvoice::class);

        if ($return) {
            return $invoice;
        }

        $data = array_replace([
            'order'       => null,
            'credit'      => false,
            'currency'    => self::CURRENCY_EUR,
            'grand_total' => 0.,
            'lines'       => [],
        ], $data);

        if (null !== $data['order']) {
            $invoice->setOrder(self::order($data['order']));
        }

        $invoice
            ->setCredit($data['credit'])
            ->setCurrency($data['currency'])
            ->setGrandTotal($data['grand_total']);

        foreach ($data['lines'] as $datum) {
            $invoice->addLine(self::invoiceLine($datum));
        }

        return $invoice;
    }

    /**
     * Creates a new invoice item.
     *
     * Defaults : [
     *     'invoice'  => null,
     *     'target'   => null,
     *     'quantity' => 1.,
     * ]
     *
     * @param OrderE\OrderInvoiceLine|array|int|string $data
     *
     * @return OrderE\OrderInvoiceLine
     */
    public static function invoiceLine($data = []): OrderE\OrderInvoiceLine
    {
        /** @var OrderE\OrderInvoiceLine $line */
        [$line, $return] = self::create($data, OrderE\OrderInvoiceLine::class);

        if ($return) {
            return $line;
        }

        $data = array_replace([
            'invoice'    => null,
            'item'       => null,
            'order'      => null,
            'adjustment' => null,
            'target'     => null,
            'quantity'   => 1.,
        ], $data);

        if (null !== $data['invoice']) {
            //$data['invoice'] = self::invoice($data['invoice']);
            $line->setInvoice(self::invoice($data['invoice']));
        }

        $line->setQuantity($data['quantity']);

        if (!isset($data['target'])) {
            if (isset($data['item'])) {
                $data['target'] = self::orderItem($data['item']);
                unset($data['item']);
            } elseif (isset($data['order'])) {
                $data['target'] = self::order($data['order']);
                unset($data['order']);
            } elseif (isset($data['adjustment'])) {
                $data['target'] = self::orderAdjustment($data['adjustment']);
                unset($data['adjustment']);
            } else {
                throw new InvalidArgumentException("Undefined invoice line target.");
            }
        }

        if ($data['target'] instanceof OrderM\OrderItemInterface) {
            $line
                ->setOrderItem($data['target'])
                ->setType(DocumentLineTypes::TYPE_GOOD);
        } elseif ($data['target'] instanceof OrderM\OrderAdjustmentInterface) {
            $line
                ->setOrderAdjustment($data['target'])
                ->setType(DocumentLineTypes::TYPE_DISCOUNT);
        } elseif ($data['target'] instanceof OrderM\OrderInterface) {
            $line->setType(DocumentLineTypes::TYPE_SHIPMENT);
        } else {
            throw new UnexpectedTypeException($data['target'], [
                OrderM\OrderInterface::class,
                OrderM\OrderItemInterface::class,
                OrderM\OrderAdjustmentInterface::class,
            ]);
        }

        return $line;
    }

    /**
     * Creates a new warehouse.
     *
     * Defaults : [
     *     'name'      => 'Foo warehouse',
     *     'countries' => [self::COUNTRY_FR],
     *     'office'    => true,
     *     'priority'  => 0,
     * ]
     *
     * @param StockE\Warehouse|array|int|string $data
     *
     * @return StockE\Warehouse
     */
    public static function warehouse($data = []): StockE\Warehouse
    {
        /** @var StockE\Warehouse $warehouse */
        [$warehouse, $return] = self::create($data, StockE\Warehouse::class);

        if ($return) {
            return $warehouse;
        }

        $data = array_replace([
            'name'      => 'Foo warehouse',
            'country'   => self::COUNTRY_FR,
            'countries' => [self::COUNTRY_FR],
            'office'    => true,
            'priority'  => 0,
        ], $data);

        $warehouse
            ->setName($data['name'])
            ->setOffice($data['office'])
            ->setPriority($data['priority'])
            ->setCountry(self::country($data['country']));

        foreach ($data['countries'] as $datum) {
            $warehouse->addCountry(self::country($datum));
        }

        return $warehouse;
    }

    /**
     * Loads the taxation entities.
     */
    private static function loadTaxes(): void
    {
        if (self::$taxesLoaded) {
            return;
        }

        self::$taxesLoaded = true;

        $load = function ($path, $factory): void {
            if (!(file_exists($path) && is_readable($path))) {
                return;
            }

            $data = Yaml::parse(file_get_contents($path));
            if (!is_array($data) || empty($data)) {
                throw new RuntimeException("File $path is invalid or empty.");
            }

            foreach ($data as $code => $datum) {
                call_user_func($factory, array_replace([
                    '_reference' => $code,
                    'code'       => $code,
                ], $datum));
            }
        };

        $map = [
            'taxes'      => 'tax',
            'tax_groups' => 'taxGroup',
            'tax_rules'  => 'taxRule',
        ];
        foreach ($map as $filename => $method) {
            $load(self::DATA_DIR . "/{$filename}.yml", [__CLASS__, $method]);
        }
    }

    /**
     * Loads the shipping entities.
     */
    private static function loadShipping(): void
    {
        if (self::$shippingLoaded) {
            return;
        }

        self::$shippingLoaded = true;

        foreach (self::SHIPMENT_METHODS as $method) {
            self::load($method);
        }

        foreach (self::SHIPMENT_ZONES as $zone) {
            self::load($zone);
        }
    }

    /**
     * Creates a date.
     *
     * @param $data
     *
     * @return DateTime
     */
    private static function date($data): DateTime
    {
        if ($data instanceof DateTime) {
            return $data;
        }

        return new DateTime($data);
    }

    /**
     * Assigns the subject to the relative.
     *
     * @param SubjectRelativeInterface          $relative
     * @param SubjectInterface|array|string|int $subject
     *
     * @return SubjectInterface;
     */
    private static function assignSubject(SubjectRelativeInterface $relative, $subject): SubjectInterface
    {
        if (!$subject instanceof SubjectInterface) {
            $subject = self::subject($subject);
        }

        $relative
            ->getSubjectIdentity()
            ->setSubject($subject)
            ->setProvider($subject::getProviderName())
            ->setIdentifier($subject->getIdentifier());

        if (empty($relative->getDesignation())) {
            $relative->setDesignation($subject->getDesignation());
        }
        if (empty($relative->getReference())) {
            $relative->setReference($subject->getReference());
        }
        if (0. === $relative->getNetPrice()) {
            $relative->setNetPrice($subject->getNetPrice());
        }
        if (0. === $relative->getWeight()) {
            $relative->setWeight($subject->getWeight());
        }
        if ($taxGroup = $relative->getTaxGroup()) {
            if ($taxGroup !== $subject->getTaxGroup()) {
                throw new LogicException("Tax group miss match.");
            }
        } else {
            $relative->setTaxGroup($subject->getTaxGroup());
        }

        return $subject;
    }

    /**
     * Builds the adjustment designation.
     *
     * @param array $data
     *
     * @return string
     */
    private static function adjustmentDesignation(array $data): string
    {
        if ($data['type'] === CommonM\AdjustmentTypes::TYPE_TAXATION) {
            if ($data['mode'] === CommonM\AdjustmentModes::MODE_PERCENT) {
                return "VAT {$data['amount']}%";
            }

            throw new InvalidArgumentException("Unexpected adjustment mode.");
        }

        if ($data['type'] === CommonM\AdjustmentTypes::TYPE_DISCOUNT) {
            if ($data['mode'] === CommonM\AdjustmentModes::MODE_PERCENT) {
                return "Discount {$data['amount']}%";
            }

            if (array_key_exists('order', $data) && $data['mode'] === CommonM\AdjustmentModes::MODE_FLAT) {
                return "Discount -{$data['amount']}";
            }

            throw new InvalidArgumentException("Unexpected adjustment mode.");
        }

        throw new InvalidArgumentException("Unexpected adjustment type.");
    }

    /**
     * Fills the given address.
     *
     * @param CommonM\AddressInterface $address
     * @param array                    $data
     */
    private static function fillAddress(CommonM\AddressInterface $address, array $data): void
    {
        $data = array_replace([
            'company'     => null,
            'street'      => 'The street',
            'complement'  => null,
            'supplement'  => null,
            'extra'       => null,
            'postal_code' => '12345',
            'city'        => 'The city',
            'country'     => self::COUNTRY_FR,
            //'state'       => null,
            //'phone'       => null,
            //'mobile'      => null,
        ], $data);

        if ($datum = $data['company']) {
            $address->setCompany($datum);
        }
        if ($datum = $data['street']) {
            $address->setStreet($datum);
        }
        if ($datum = $data['complement']) {
            $address->setComplement($datum);
        }
        if ($datum = $data['supplement']) {
            $address->setSupplement($datum);
        }
        if ($datum = $data['extra']) {
            $address->setExtra($datum);
        }
        if ($datum = $data['postal_code']) {
            $address->setPostalCode($datum);
        }
        if ($datum = $data['city']) {
            $address->setCity($datum);
        }
        if ($datum = $data['country']) {
            $address->setCountry(Fixture::country($datum));
        }
        /* TODO if ($datum = $data['state']) {
            $address->setState(Fixture::state($datum));
        }*/
        /* TODO if ($datum = $data['phone']) {
            $address->setPhone(Fixture::phone($datum));
        }
        if ($datum = $data['mobile']) {
            $address->setMobile(Fixture::phone($datum));
        }*/
    }

    /**
     * @param object|array|int|string $data
     * @param string                  $class
     *
     * @return array [object, bool]
     */
    private static function create($data, string $class): array
    {
        if ($data instanceof $class) {
            return [$data, true];
        }

        if (!empty($data) && (is_int($data) || is_string($data))) {
            return [self::get($data, $class), true];
        }

        if (!is_array($data)) {
            throw new InvalidArgumentException("Expected object, array, int or string");
        }

        if (isset($data['_reference']) && self::has($data['_reference'])) {
            return [self::get($data['_reference']), true];
        }

        $object = new $class();

        self::register($object, $data);

        return [$object, false];
    }

    private static function load(string $reference): void
    {
        if (isset(self::$references[$reference])) {
            return;
        }

        $datum = call_user_func(sprintf('%s::%s', Data::class, $reference));

        if (!isset($datum['_reference']) || $datum['_reference'] !== $reference) {
            throw new LogicException("'$reference' data fixture : '_reference' miss match.");
        }

        if (!isset($datum['_type'])) {
            throw new LogicException("Can't load data fixture '$reference' because '_type' is not defined.");
        }

        call_user_func(sprintf('%s::%s', __CLASS__, $datum['_type']), $datum);
    }

    /**
     * Registers the fixture object.
     *
     * @param object $object
     * @param array  $data
     */
    private static function register(object $object, array $data): void
    {
        $data = array_replace([
            '_id'           => true,
            '_reference'    => null,
            '_dependencies' => [],
        ], $data);

        if (!empty($dependencies = $data['_dependencies'])) {
            foreach ($dependencies as $reference) {
                self::load($reference);
            }
        }

        if ($id = $data['_id']) {
            $class = get_class($object);

            if (!isset(self::$ids[$class])) {
                $r = new \ReflectionClass($class);
                $p = $r->getProperty('id');
                $p->setAccessible(true);

                self::$ids[$class] = [
                    'id'         => 0,
                    'references' => [],
                    'generator'  => function (object $object, $id) use ($p, $class) {
                        $id = is_int($id) ? $id : ++self::$ids[$class]['id'];

                        $p->setValue($object, $id);

                        self::$ids[$class]['references'][$id] = $object;
                    },
                ];
            }

            (self::$ids[$class]['generator'])($object, $id);
        }

        if ($reference = $data['_reference']) {
            if (isset(self::$references[$reference])) {
                throw new LogicException("A fixture is already registered for reference '$reference'.");
            }

            self::$references[$reference] = $object;
        }
    }

    /**
     * Returns whether a fixture entity is registered for the given reference.
     *
     * @param string $reference
     *
     * @return bool
     */
    public static function has(string $reference): bool
    {
        return isset(self::$references[$reference]);
    }

    /**
     * Returns the registered fixture entity by its id and class, of reference.
     *
     * @param int|string $id    The id or the reference
     * @param string     $class The class if searching by id
     *
     * @return object
     */
    public static function get($id, string $class = null): object
    {
        if (is_int($id)) {
            if (empty($class)) {
                throw new LogicException("You must provide a class to find a fixture by its id.");
            }

            if (!isset(self::$ids[$class])) {
                throw new RuntimeException("No fixtures for class $class.");
            }

            if (!isset(self::$ids[$class]['references'][$id])) {
                throw new RuntimeException("No fixture found for id $id and class $class.");
            }

            return self::$ids[$class]['references'][$id];
        }

        if (is_string($id)) {
            if (isset(self::$references[$id])) {
                return self::$references[$id];
            }

            try {
                self::load($id);

                return self::$references[$id];
            } catch (\Throwable $t) {
            }

            throw new RuntimeException("No fixtures found for reference '$id'.");
        }

        throw new LogicException("Expected 'id' as int or string.");
    }

    /**
     * Clears the fixture entities.
     */
    public static function clear(): void
    {
        foreach (self::$ids as &$entity) {
            $entity['id'] = 0;
            $entity['references'] = [];
        }

        self::$references = [];
        self::$taxesLoaded = false;
        self::$shippingLoaded = false;
    }
}
