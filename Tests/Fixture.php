<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Tests;

use Acme\Product\Entity as Acme;
use DateTime;
use DateTimeInterface;
use Decimal\Decimal;
use Ekyna\Component\Commerce\Common\Context\Context;
use Ekyna\Component\Commerce\Common\Context\ContextInterface;
use Ekyna\Component\Commerce\Common\Entity as CommonE;
use Ekyna\Component\Commerce\Common\Model as CommonM;
use Ekyna\Component\Commerce\Customer\Entity as CustomerE;
use Ekyna\Component\Commerce\Document\Model\DocumentLineTypes;
use Ekyna\Component\Commerce\Exception\UnexpectedTypeException;
use Ekyna\Component\Commerce\Invoice\Model\InvoiceStates;
use Ekyna\Component\Commerce\Order\Entity as OrderE;
use Ekyna\Component\Commerce\Order\Model as OrderM;
use Ekyna\Component\Commerce\Payment\Model\PaymentStates;
use Ekyna\Component\Commerce\Pricing\Entity as PricingE;
use Ekyna\Component\Commerce\Pricing\Model\VatDisplayModes;
use Ekyna\Component\Commerce\Shipment\Entity as ShipmentE;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentStates;
use Ekyna\Component\Commerce\Stock\Entity as StockE;
use Ekyna\Component\Commerce\Stock\Model as StockM;
use Ekyna\Component\Commerce\Stock\Model\StockSubjectModes;
use Ekyna\Component\Commerce\Stock\Model\StockSubjectStates;
use Ekyna\Component\Commerce\Stock\Resolver\StockUnitStateResolver;
use Ekyna\Component\Commerce\Stock\Resolver\StockUnitStateResolverInterface;
use Ekyna\Component\Commerce\Subject\Model\SubjectInterface;
use Ekyna\Component\Commerce\Subject\Model\SubjectRelativeInterface;
use Ekyna\Component\Commerce\Supplier\Entity as SupplierE;
use InvalidArgumentException;
use libphonenumber\PhoneNumber;
use libphonenumber\PhoneNumberUtil;
use LogicException;
use ReflectionClass;
use RuntimeException;
use Symfony\Component\Intl\Countries;
use Symfony\Component\Intl\Currencies;
use Symfony\Component\Yaml\Yaml;
use Throwable;

use function is_object;

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

    private static ?StockUnitStateResolverInterface $stockUnitStateResolver = null;
    private static bool                             $taxesLoaded            = false;
    private static bool                             $shippingLoaded         = false;
    private static array                            $ids                    = [];
    private static array                            $references             = [];


    /**
     * Resolves the stock unit state.
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
     * @param PricingE\Tax|int|string|array{
     *     name:    string,
     *     code:    string,
     *     country: string|int
     * } $data The tax object, ID, reference or data.
     */
    public static function tax(PricingE\Tax|int|string|array $data = []): PricingE\Tax
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
            throw new LogicException('Invalid tax rate : ' . $data['rate']);
        }

        $tax
            ->setCode($data['code'])
            ->setName($data['name'])
            ->setRate(self::decimal($data['rate']));

        if (null !== $datum = $data['country']) {
            $tax->setCountry(self::country($datum));
        }

        return $tax;
    }

    /**
     * Creates a tax group.
     *
     * @param PricingE\TaxGroup|int|string|array{
     *     name:    string,
     *     code:    string,
     *     default: bool,
     *     taxes:   array,
     * } $data The tag group object, ID, reference or data.
     */
    public static function taxGroup(PricingE\TaxGroup|int|string|array $data = []): PricingE\TaxGroup
    {
        self::loadTaxes();

        /** @var PricingE\TaxGroup $tax */
        [$group, $return] = self::create($data, PricingE\TaxGroup::class);

        if ($return) {
            return $group;
        }

        $data = array_replace([
            'name'    => null,
            'code'    => null,
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
     * @param PricingE\TaxRule|int|string|array{
     *     code:     string,
     *     name:     string,
     *     customer: bool,
     *     business: bool,
     *     sources:  array,
     *     targets:  array,
     *     taxes:    array,
     *     priority: int,
     * } $data The tax rule object, ID, reference or data.
     */
    public static function taxRule(PricingE\TaxRule|int|string|array $data = []): PricingE\TaxRule
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
     * @param CommonE\Currency|int|string|null|array{
     *     name:    string,
     *     code:    string,
     *     enabled: bool
     * } $data The currency object, ID, reference or data.
     */
    public static function currency(CommonE\Currency|int|string|null|array $data = null): CommonE\Currency
    {
        if (null === $data) {
            $data = self::CURRENCY_EUR;
        }
        if (is_string($data) && preg_match('~^[a-zA-Z]{2,3}$~', $data)) {
            $code = strtoupper($data);
            $reference = 'currency_' . $code;
            if (self::has($reference)) {
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

        if (empty($data['code'])) {
            throw new LogicException('Country code is required.');
        }

        $data['code'] = strtoupper($data['code']);

        if (empty($data['name'])) {
            if (null === $name = Currencies::getName($data['code'])) {
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
     * @param array{
     *     customer_group:   mixed,
     *     invoice_country:  string,
     *     delivery_country: string,
     *     shipping_country: string,
     *     currency:         string,
     *     locale:           string,
     *     vat_display_mode: string,
     *     business:         bool,
     *     tax_exempt:       bool,
     *     date:             DateTimeInterface|string,
     *     admin:            bool,
     * } $data The context data.
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
     * @psalm-param array{
     *     name:    string,
     *     code:    string,
     *     enabled: bool,
     * } $data
     */
    public static function country(CommonE\Country|array|int|string|null $data = null): CommonE\Country
    {
        if (null === $data) {
            $data = self::COUNTRY_FR;
        }
        if (is_string($data) && preg_match('~^[a-zA-Z]{2,3}$~', $data)) {
            $code = strtoupper($data);
            $reference = 'country_' . $code;
            if (self::has($reference)) {
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

        if (empty($data['code'])) {
            throw new LogicException('Country code is required.');
        }

        $data['code'] = strtoupper($data['code']);

        if (empty($data['name'])) {
            if (null === $name = Countries::getName($data['code'])) {
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
     * @psalm-param array{
     *     group:               mixed,
     *     company:             string,
     *     first_name:          string,
     *     last_name:           string,
     *     email:               string,
     *     credit_balance:      string|int|float,
     *     outstanding_balance: string|int|float,
     * } $data
     */
    public static function customer(CustomerE\Customer|array|int|string $data = []): CustomerE\Customer
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
            'credit_balance'      => 0,
            'outstanding_balance' => 0,
        ], $data);

        $customer
            ->setCompany($data['company'])
            ->setEmail($data['email'])
            ->setCreditBalance(self::decimal($data['credit_balance']))
            ->setOutstandingBalance(self::decimal($data['outstanding_balance']))
            ->setFirstName($data['first_name'])
            ->setLastName($data['last_name']);

        if (null !== $datum = $data['group']) {
            $customer->setCustomerGroup(self::customerGroup($datum));
        }

        return $customer;
    }

    /**
     * Creates a customer.
     *
     * @psalm-param array{
     *     name:     string,
     *     default:  bool,
     *     business: bool
     * } $data
     */
    public static function customerGroup(CustomerE\CustomerGroup|array|int|string $data = []): CustomerE\CustomerGroup
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
     * @psalm-param array{
     *     subject:        mixed,
     *     item:           mixed,
     *     eda:            DateTimeInterface|string,
     *     state:          string,
     *     net_price:      string|int|float,
     *     shipping_price: string|int|float,
     *     ordered:        string|int|float,
     *     received:       string|int|float,
     *     adjusted:       string|int|float,
     *     sold:           string|int|float,
     *     shipped:        string|int|float,
     *     locked:         string|int|float,
     *     assignments:    array,
     *     adjustments:    array,
     * } $data
     */
    public static function stockUnit(Acme\StockUnit|array|int|string $data = []): Acme\StockUnit
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
            'state'          => null,
            'net_price'      => 0,
            'shipping_price' => 0,
            'ordered'        => 0,
            'received'       => 0,
            'adjusted'       => 0,
            'sold'           => 0,
            'shipped'        => 0,
            'locked'         => 0,
            'assignments'    => [],
            'adjustments'    => [],
        ], $data);

        $subject = null;
        if (null !== $datum = $data['subject']) {
            $subject = self::subject($datum);
        }

        if (null !== $datum = $data['item']) {
            $unit->setSupplierOrderItem($item = self::supplierOrderItem($datum));

            if ($item->getSubjectIdentity()->hasIdentity()) {
                $s = $item->getSubjectIdentity()->getSubject();
                if ($subject) {
                    if ($s !== $subject) {
                        throw new LogicException('Subject miss match');
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
            ->setNetPrice(self::decimal($data['net_price']))
            ->setShippingPrice(self::decimal($data['shipping_price']))
            ->setOrderedQuantity(self::decimal($data['ordered']))
            ->setReceivedQuantity(self::decimal($data['received']))
            ->setAdjustedQuantity(self::decimal($data['adjusted']))
            ->setSoldQuantity(self::decimal($data['sold']))
            ->setShippedQuantity(self::decimal($data['shipped']))
            ->setLockedQuantity(self::decimal($data['locked']));

        if (null !== $datum = $data['eda']) {
            $unit->setEstimatedDateOfArrival(self::date($datum));
        }

        foreach ($data['assignments'] as $datum) {
            $unit->addStockAssignment(self::stockAssignment($datum));
        }

        foreach ($data['adjustments'] as $datum) {
            $unit->addStockAdjustment(self::stockAdjustment($datum));
        }

        if (null !== $datum = $data['state']) {
            $unit->setState($datum);
        } else {
            self::resolveStockUnitState($unit);
        }

        return $unit;
    }

    /**
     * Creates a stock assignment.
     *
     * @psalm-param array{
     *     unit:    mixed,
     *     item:    mixed,
     *     sold:    string|int|float,
     *     shipped: string|int|float,
     *     locked:  string|int|float,
     * } $data
     */
    public static function stockAssignment(
        OrderE\OrderItemStockAssignment|array|int|string $data = []
    ): OrderE\OrderItemStockAssignment {
        /** @var OrderE\OrderItemStockAssignment $assignment */
        [$assignment, $return] = self::create($data, OrderE\OrderItemStockAssignment::class);

        if ($return) {
            return $assignment;
        }

        $data = array_replace([
            'unit'    => null,
            'item'    => null,
            'sold'    => 0,
            'shipped' => 0,
            'locked'  => 0,
        ], $data);

        if (null !== $datum = $data['unit']) {
            $assignment->setStockUnit(self::stockUnit($datum));
        }

        if (null !== $datum = $data['item']) {
            $assignment->setOrderItem(self::orderItem($datum));
        }

        $assignment
            ->setSoldQuantity(self::decimal($data['sold']))
            ->setShippedQuantity(self::decimal($data['shipped']))
            ->setLockedQuantity(self::decimal($data['locked']));

        return $assignment;
    }

    /**
     * Creates a new stock adjustment.
     *
     * @psalm-param array{
     *     unit:     mixed,
     *     quantity: string|int|float,
     *     debit:    bool,
     * } $data
     */
    public static function stockAdjustment(StockE\StockAdjustment|array|int|string $data = []): StockE\StockAdjustment
    {
        /** @var StockE\StockAdjustment $adjustment */
        [$adjustment, $return] = self::create($data, StockE\StockAdjustment::class);

        if ($return) {
            return $adjustment;
        }

        $data = array_replace([
            'unit'     => null,
            'quantity' => 1,
            'debit'    => false,
        ], $data);

        if (null !== $datum = $data['unit']) {
            $adjustment->setStockUnit(self::stockUnit($datum));
        }

        $adjustment
            ->setQuantity(self::decimal($data['quantity']))
            ->setReason($data['debit']
                ? StockM\StockAdjustmentReasons::REASON_DEBIT
                : StockM\StockAdjustmentReasons::REASON_CREDIT
            );

        return $adjustment;
    }

    /**
     * Creates a new subject (acme product).
     *
     * @psalm-param array{
     *     designation:  string,
     *     reference:    string,
     *     price:        string|int|float,
     *     weight:       string|int|float,
     *     mode: string,
     *     state: string,
     *     in: string|int|float,
     *     available: string|int|float,
     *     virtual: string|int|float,
     *     eda: DateTimeInterface|string,
     * } $data
     */
    public static function subject(Acme\Product|array|int|string $data = []): Acme\Product
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
            'weight'      => 0.8,
            'tax_group'   => self::TAX_GROUP_NORMAL,
            'mode'        => StockSubjectModes::MODE_DISABLED,
            'state'       => StockSubjectStates::STATE_IN_STOCK,
            'in'          => 0,
            'available'   => 0,
            'virtual'     => 0,
            'eda'         => null,
        ], $data);

        $subject
            ->setDesignation($data['designation'])
            ->setReference($data['reference'])
            ->setNetPrice(self::decimal($data['price']))
            ->setWeight(self::decimal($data['weight']))
            ->setStockMode($data['mode'])
            ->setStockState($data['state'])
            ->setInStock(self::decimal($data['in']))
            ->setAvailableStock(self::decimal($data['available']))
            ->setVirtualStock(self::decimal($data['virtual']));

        if (null !== $datum = $data['tax_group']) {
            $subject->setTaxGroup(self::taxGroup($datum));
        }

        if (null !== $datum = $data['eda']) {
            $subject->setEstimatedDateOfArrival(self::date($datum));
        }

        return $subject;
    }

    /**
     * Creates a new supplier.
     *
     * @psalm-param array{
     *     name:     string,
     *     currency: string,
     *     tax:      mixed,
     *     carrier:  mixed,
     * } $data
     */
    public static function supplier(SupplierE\Supplier|array|int|string $data = []): SupplierE\Supplier
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
     * @psalm-param array{
     *     name: string,
     *     tax:  mixed,
     * } $data
     */
    public static function supplierCarrier(SupplierE\SupplierCarrier|array|int|string $data = []
    ): SupplierE\SupplierCarrier {
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
     * @see Fixture::fillAddress()
     */
    public static function supplierAddress(SupplierE\SupplierAddress|array|int|string $data = []
    ): SupplierE\SupplierAddress {
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
     * @psalm-param array{
     *     supplier:    mixed,
     *     subject:     mixed,
     *     tax_group:   string,
     *     designation: string,
     *     reference:   string,
     *     price:       string|int|float,
     *     weight:      string|int|float,
     *     available:   string|int|float,
     *     ordered:     string|int|float,
     *     eda:         DateTimeInterface|string,
     * } $data
     */
    public static function supplierProduct(SupplierE\SupplierProduct|array|int|string $data = []
    ): SupplierE\SupplierProduct {
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
            'price'       => 0,
            'weight'      => 0,
            'available'   => 0,
            'ordered'     => 0,
            'eda'         => null,
        ], $data);

        $product
            ->setDesignation($data['designation'])
            ->setReference($data['reference'])
            ->setNetPrice(self::decimal($data['price']))
            ->setWeight(self::decimal($data['weight']))
            ->setAvailableStock(self::decimal($data['available']))
            ->setOrderedStock(self::decimal($data['ordered']));

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
     * @psalm-param array{
     *     currency:        string,
     *     shipping_cost:   string|int|float,
     *     discount_total:  string|int|float,
     *     tax_total:       string|int|float,
     *     payment_total:   string|int|float,
     *     customs_tax:     string|int|float,
     *     customs_vat:     string|int|float,
     *     forwarder_fee:   string|int|float,
     *     forwarder_total: string|int|float,
     *     created_at:      DateTimeInterface|string,
     *     ordered_at:      DateTimeInterface|string,
     *     supplier:        mixed,
     *     carrier:         mixed,
     *     warehouse:       mixed,
     *     items:           array,
     * } $data
     */
    public static function supplierOrder(SupplierE\SupplierOrder|array|int|string $data = []): SupplierE\SupplierOrder
    {
        /** @var SupplierE\SupplierOrder $order */
        [$order, $return] = self::create($data, SupplierE\SupplierOrder::class);

        if ($return) {
            return $order;
        }

        $data = array_replace([
            'currency'        => self::CURRENCY_EUR,
            'shipping_cost'   => 0,
            'discount_total'  => 0,
            'tax_total'       => 0,
            'payment_total'   => 0,
            'customs_tax'     => 0,
            'customs_vat'     => 0,
            'forwarder_fee'   => 0,
            'forwarder_total' => 0,
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
            ->setShippingCost(self::decimal($data['shipping_cost']))
            ->setDiscountTotal(self::decimal($data['discount_total']))
            ->setTaxTotal(self::decimal($data['tax_total']))
            ->setPaymentTotal(self::decimal($data['payment_total']))
            ->setCustomsTax(self::decimal($data['customs_tax']))
            ->setCustomsVat(self::decimal($data['customs_vat']))
            ->setForwarderFee(self::decimal($data['forwarder_fee']))
            ->setForwarderTotal(self::decimal($data['forwarder_total']));

        if (null !== $datum = $data['exchange_rate']) {
            $order->setExchangeRate(self::decimal($datum));
        }

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
     * @psalm-param array{
     *     order:       mixed,
     *     product:     mixed,
     *     subject:     mixed,
     *     unit:        mixed,
     *     tax_group:   mixed,
     *     designation: string,
     *     reference:   string,
     *     price:       string|int|float,
     *     weight:      string|int|float,
     *     quantity:    string|int|float,
     *     packing:     string|int|float,
     * } $data
     */
    public static function supplierOrderItem(SupplierE\SupplierOrderItem|array|int|string $data = []
    ): SupplierE\SupplierOrderItem {
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
            'price'       => 0,
            'weight'      => 0,
            'quantity'    => 1,
            'packing'     => 1,
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
                throw new LogicException('Subject miss match');
            }
        }
        if ($unit && $s = $unit->getProduct()) {
            if (!$subject) {
                $subject = $s;
            } elseif ($subject !== $s) {
                throw new LogicException('Subject miss match');
            }
        }

        $item
            ->setDesignation($data['designation'])
            ->setReference($data['reference'])
            ->setNetPrice(self::decimal($data['price']))
            ->setWeight(self::decimal($data['weight']))
            ->setQuantity(self::decimal($data['quantity']))
            ->setPacking(self::decimal($data['packing']));

        if ($subject) {
            self::assignSubject($item, $subject);
        }

        return $item;
    }

    /**
     * Creates a payment.
     *
     * @psalm-param array{
     *     order:         mixed,
     *     method:        mixed,
     *     state:         string,
     *     currency:      string,
     *     amount:        string|int|float,
     *     exchange_rate: string|int|float,
     *     exchange_date: DateTimeInterface|string,
     * } $data
     */
    public static function payment(OrderE\OrderPayment|array|int|string $data = []): OrderE\OrderPayment
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
            'amount'        => 0,
            'exchange_rate' => 1,
            'exchange_date' => 'now',
        ], $data);

        if (null !== $data['order']) {
            $payment->setOrder(self::order($data['order']));
        }

        if (null !== $data['method']) {
            $payment->setMethod(self::paymentMethod($data['method']));
        }

        $payment
            ->setCurrency(self::currency($data['currency']))
            ->setState($data['state'])
            ->setRefund($data['refund'])
            ->setAmount(self::decimal($data['amount']))
            ->setRealAmount(self::decimal($data['amount']))
            ->setExchangeRate(self::decimal($data['exchange_rate']));

        if (null !== $datum = $data['exchange_date']) {
            $payment->setExchangeDate(self::date($datum));
        }

        return $payment;
    }

    /**
     * Creates a payment method.
     *
     * @param Acme\PaymentMethod|int|string|array{
     *     enabled:     bool,
     *     available:   bool,
     *     manual:      bool,
     *     outstanding: bool,
     *     credit:      bool,
     * } $data The payment method object, ID, reference or data.
     */
    public static function paymentMethod(Acme\PaymentMethod|int|string|array $data = []): Acme\PaymentMethod
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
     * @param OrderE\Order|int|string|array{
     *     currency:             string,
     *     state:                string,
     *     customer:             mixed,
     *     invoice_address:      mixed,
     *     delivery_address:     mixed,
     *     weight_total:         string|int|float,
     *     shipment_weight:      string|int|float,
     *     shipment_amount:      string|int|float,
     *     grand_total:          string|int|float,
     *     deposit_total:        string|int|float,
     *     pending_total:        string|int|float,
     *     paid_total:           string|int|float,
     *     outstanding_accepted: string|int|float,
     *     outstanding_expired:  string|int|float,
     *     exchange_rate:        string|int|float,
     *     exchange_date:        DateTimeInterface|string,
     *     invoice_total:        string|int|float,
     *     credit_total:         string|int|float,
     *     created_at:           DateTimeInterface|string,
     *     items:                array,
     *     discounts:            array,
     *     taxes:                array,
     *     payments:             array,
     *     shipments:            array,
     *     invoices:             array,
     * } $data The order object, ID, reference or data.
     */
    public static function order(OrderE\Order|int|string|array $data = []): OrderE\Order
    {
        /** @var OrderE\Order $order */
        [$order, $return] = self::create($data, OrderE\Order::class);

        if ($return) {
            return $order;
        }

        $data = array_replace([
            'currency'             => self::CURRENCY_EUR,
            'state'                => OrderM\OrderStates::STATE_NEW,
            'payment_state'        => PaymentStates::STATE_NEW,
            'shipment_state'       => ShipmentStates::STATE_NEW,
            'invoice_state'        => InvoiceStates::STATE_NEW,
            'customer'             => null,
            'customer_group'       => null,
            'vat_valid'            => false,
            'vat_number'           => null,
            'invoice_address'      => null,
            'delivery_address'     => null,
            'same_address'         => true,
            'shipment_method'      => null,
            'shipment_weight'      => null,
            'shipment_amount'      => 0,
            'weight_total'         => 0,
            'grand_total'          => 0,
            'deposit_total'        => 0,
            'pending_total'        => 0,
            'paid_total'           => 0,
            'outstanding_accepted' => 0,
            'outstanding_expired'  => 0,
            'exchange_rate'        => null,
            'exchange_date'        => null,
            'invoice_total'        => 0,
            'credit_total'         => 0,
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
            ->setPaymentState($data['payment_state'])
            ->setShipmentState($data['shipment_state'])
            ->setInvoiceState($data['invoice_state'])
            ->setVatValid($data['vat_valid'])
            ->setVatNumber($data['vat_number'])
            ->setWeightTotal(self::decimal($data['weight_total']))
            ->setShipmentWeight($data['shipment_weight'] ? self::decimal($data['shipment_weight']) : null)
            ->setShipmentAmount(self::decimal($data['shipment_amount']))
            ->setGrandTotal(self::decimal($data['grand_total']))
            ->setDepositTotal(self::decimal($data['deposit_total']))
            ->setPendingTotal(self::decimal($data['pending_total']))
            ->setPaidTotal(self::decimal($data['paid_total']))
            ->setOutstandingAccepted(self::decimal($data['outstanding_accepted']))
            ->setOutstandingExpired(self::decimal($data['outstanding_expired']))
            ->setExchangeRate($data['exchange_rate'] ? self::decimal($data['exchange_rate']) : null)
            ->setInvoiceTotal(self::decimal($data['invoice_total']))
            ->setCreditTotal(self::decimal($data['credit_total']));

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
     * @see Fixture::fillAddress()
     */
    public static function orderAddress(OrderE\OrderAddress|array|int|string $data = []): OrderE\OrderAddress
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
     * @param OrderE\OrderItem|int|string|array{
     *     order:       mixed,
     *     parent:      mixed,
     *     subject:     mixed,
     *     tax_group:   mixed,
     *     designation: string,
     *     reference:   string,
     *     price:       string|int|float,
     *     weight:      string|int|float,
     *     quantity:    string|int|float,
     *     private:     bool,
     *     compound:    bool,
     *     immutable:   bool,
     *     discounts:   array,
     *     taxes:       array,
     *     children:    array,
     *     assignments: array,
     * } $data The item object, ID, reference or data.
     */
    public static function orderItem(OrderE\OrderItem|int|string|array $data = []): OrderE\OrderItem
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
            'price'       => 0,
            'weight'      => 0,
            'quantity'    => 1,
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
            ->setNetPrice(self::decimal($data['price']))
            ->setWeight(self::decimal($data['weight']))
            ->setQuantity(self::decimal($data['quantity']))
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
     * @psalm-param array{
     *     type:    string,
     *     mode:    string,
     *     item:    mixed,
     *     ammount: string|int|float,
     *     source:  string,
     * } $data
     */
    public static function orderItemAdjustment(OrderE\OrderItemAdjustment|array|int|string $data = []
    ): OrderE\OrderItemAdjustment {
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
            ->setAmount(self::decimal($data['amount']))
            ->setSource($data['source']);

        return $adjustment;
    }

    /**
     * Creates a new order adjustment.
     *
     * @param OrderE\OrderAdjustment|int|string|array{
     *     type:    string,
     *     mode:    string,
     *     order:    mixed,
     *     ammount: string|int|float,
     *     source:  string,
     * } $data The adjustment object, id, reference or data.
     */
    public static function orderAdjustment(OrderE\OrderAdjustment|int|string|array $data = []): OrderE\OrderAdjustment
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
            'amount' => 0,
            'source' => null,
        ], $data);

        if (null !== $datum = $data['order']) {
            $adjustment->setOrder(self::order($datum));
        }

        $adjustment
            ->setType($data['type'])
            ->setMode($data['mode'])
            ->setDesignation(self::adjustmentDesignation($data))
            ->setAmount(self::decimal($data['amount']))
            ->setSource($data['source']);

        return $adjustment;
    }

    /**
     * Creates a new order taxation adjustment (for shipment).
     *
     * @param Decimal $amount
     *
     * @return OrderE\OrderAdjustment
     *
     * @deprecated Use Fixture::orderAdjustment
     * @TODO       Remove
     */
    public static function orderTaxationAdjustment(Decimal $amount): OrderE\OrderAdjustment
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
     * @param Decimal $amount
     * @param bool    $flat
     *
     * @return OrderE\OrderAdjustment
     *
     * @deprecated Use Fixture::orderAdjustment
     * @TODO       Remove
     */
    public static function orderDiscountAdjustment(Decimal $amount, bool $flat = false): OrderE\OrderAdjustment
    {
        $adjustment = new OrderE\OrderAdjustment();
        $adjustment
            ->setType(CommonM\AdjustmentTypes::TYPE_DISCOUNT)
            ->setMode($flat ? CommonM\AdjustmentModes::MODE_FLAT : CommonM\AdjustmentModes::MODE_PERCENT)
            ->setDesignation($flat ? 'Discount' : "Discount $amount%")
            ->setAmount($amount);

        return $adjustment;
    }

    /**
     * Creates a new shipment.
     *
     * @psalm-param array{
     *     order:  mixed,
     *     method: mixed,
     *     return: bool,
     *     items:  array,
     * } $data
     */
    public static function shipment(OrderE\OrderShipment|array|int|string $data = []): OrderE\OrderShipment
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
     * Creates a new address.
     *
     * @see Fixture::fillAddress()
     */
    public static function address(array $data = []): CommonM\Address
    {
        $address = new CommonM\Address();

        self::fillAddress($address, $data);

        return $address;
    }

    /**
     * Creates a phone number.
     */
    public static function phone(string $number): PhoneNumber
    {
        return PhoneNumberUtil::getInstance()->parse($number);
    }

    /**
     * Creates a new shipment item.
     *
     * @psalm-param array{
     *     shipment: mixed,
     *     item:     mixed,
     *     quantity: string|int|float,
     * } $data
     */
    public static function shipmentItem(OrderE\OrderShipmentItem|array|int|string $data = []): OrderE\OrderShipmentItem
    {
        /** @var OrderE\OrderShipmentItem $item */
        [$item, $return] = self::create($data, OrderE\OrderShipmentItem::class);

        if ($return) {
            return $item;
        }

        $data = array_replace([
            'shipment' => null,
            'item'     => null,
            'quantity' => 1,
        ], $data);

        if (null !== $datum = $data['shipment']) {
            $item->setShipment(self::shipment($datum));
        }

        if (null !== $datum = $data['item']) {
            $item->setOrderItem(self::orderItem($datum));
        }

        $item->setQuantity(self::decimal($data['quantity']));

        return $item;
    }

    /**
     * Creates a shipment method.
     *
     * @psalm-param array{
     *     tax_group: mixed,
     *     name:      string,
     *     enabled:   bool,
     *     available: bool,
     *     platform:  string,
     *     gateway:   string,
     *     prices:    array,
     * } $data
     */
    public static function shipmentMethod(ShipmentE\ShipmentMethod|array|int|string $data = []
    ): ShipmentE\ShipmentMethod {
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
     * @psalm-param array{
     *     method: mixed,
     *     zone:   mixed,
     *     weight: string|int|float,
     *     price:  string|int|float,
     * } $data
     */
    public static function shipmentPrice(ShipmentE\ShipmentPrice|array|int|string $data = []): ShipmentE\ShipmentPrice
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
            'weight' => 0,
            'price'  => 0,
        ], $data);

        $price
            ->setWeight(self::decimal($data['weight']))
            ->setNetPrice(self::decimal($data['price']));

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
     * @psalm-param array{
     *     name:            string,
     *     methods:          array,
     *     countries:        array,
     *     customer_groups:  array,
     *     base_total:       string|int|float,
     *     vat_mode:         string,
     *     start_at:         DateTimeInterface|string,
     *     end_at:           DateTimeInterface|string,
     *     price:            string|int|float,
     * } $data
     */
    public static function shipmentRule(ShipmentE\ShipmentRule|array|int|string $data = []): ShipmentE\ShipmentRule
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
            'base_total'      => 0,
            'vat_mode'        => VatDisplayModes::MODE_NET,
            'start_at'        => null,
            'end_at'          => null,
            'price'           => 0,
        ], $data);

        $rule
            ->setName($data['name'])
            ->setBaseTotal(self::decimal($data['base_total']))
            ->setVatMode($data['vat_mode'])
            ->setStartAt(self::date($data['start_at']))
            ->setEndAt(self::date($data['end_at']))
            ->setNetPrice(self::decimal($data['price']));

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
     * @psalm-param array{
     *     name:      string,
     *     countries: array,
     *     prices:    array,
     * } $data
     */
    public static function shipmentZone(ShipmentE\ShipmentZone|array|int|string $data = []): ShipmentE\ShipmentZone
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
     * @psalm-param array{
     *     order:        mixed,
     *     credit:       bool,
     *     ignore_stock: bool,
     *     currency:     string,
     *     grand_total:  string|int|float,
     *     lines:        array,
     * } $data
     */
    public static function invoice(OrderE\OrderInvoice|array|int|string $data = []): OrderE\OrderInvoice
    {
        /** @var OrderE\OrderInvoice $invoice */
        [$invoice, $return] = self::create($data, OrderE\OrderInvoice::class);

        if ($return) {
            return $invoice;
        }

        $data = array_replace([
            'order'        => null,
            'credit'       => false,
            'ignore_stock' => false,
            'currency'     => self::CURRENCY_EUR,
            'grand_total'  => 0,
            'lines'        => [],
        ], $data);

        if ($data['ignore_stock'] && !$data['credit']) {
            throw new LogicException("Non credit invoice can't ignore stock.");
        }

        if (null !== $data['order']) {
            $invoice->setOrder(self::order($data['order']));
        }

        $invoice
            ->setCredit($data['credit'])
            ->setIgnoreStock($data['ignore_stock'])
            ->setCurrency($data['currency'])
            ->setGrandTotal(self::decimal($data['grand_total']));

        foreach ($data['lines'] as $datum) {
            $invoice->addLine(self::invoiceLine($datum));
        }

        return $invoice;
    }

    /**
     * Creates a new invoice item.
     *
     * @psalm-param array{
     *     invoice:    mixed,
     *     item:       mixed,
     *     order:      mixed,
     *     adjustment: mixed,
     *     target:     mixed,
     *     quantity:   string|int|float,
     * } $data
     */
    public static function invoiceLine(OrderE\OrderInvoiceLine|array|int|string $data = []): OrderE\OrderInvoiceLine
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
            'quantity'   => 1,
        ], $data);

        if (null !== $data['invoice']) {
            $line->setInvoice(self::invoice($data['invoice']));
        }

        $line->setQuantity(self::decimal($data['quantity']));

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
                throw new InvalidArgumentException('Undefined invoice line target.');
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
     * @psalm-param array{
     *     name:      string,
     *     country:   mixed,
     *     countries: array,
     *     office:    bool,
     *     priority:  int,
     * } $data
     */
    public static function warehouse(StockE\Warehouse|array|int|string $data = []): StockE\Warehouse
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
                throw new RuntimeException("File $path not found.");
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
            $load(self::DATA_DIR . "/$filename.yaml", [__CLASS__, $method]);
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
     */
    private static function date(DateTimeInterface|string $data): DateTimeInterface
    {
        if ($data instanceof DateTimeInterface) {
            return $data;
        }

        return new DateTime($data);
    }

    /**
     * Assigns the subject to the relative.
     */
    private static function assignSubject(
        SubjectRelativeInterface          $relative,
        SubjectInterface|array|string|int $subject
    ): void {
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
        if ($relative->getNetPrice()->isZero()) {
            $relative->setNetPrice(clone $subject->getNetPrice());
        }
        if ($relative->getWeight()->isZero()) {
            $relative->setWeight(clone $subject->getWeight());
        }
        if ($taxGroup = $relative->getTaxGroup()) {
            if ($taxGroup !== $subject->getTaxGroup()) {
                throw new LogicException('Tax group miss match.');
            }
        } else {
            $relative->setTaxGroup($subject->getTaxGroup());
        }
    }

    /**
     * Builds the adjustment designation.
     */
    private static function adjustmentDesignation(array $data): string
    {
        if ($data['type'] === CommonM\AdjustmentTypes::TYPE_TAXATION) {
            if ($data['mode'] === CommonM\AdjustmentModes::MODE_PERCENT) {
                return "VAT {$data['amount']}%";
            }

            throw new InvalidArgumentException('Unexpected adjustment mode.');
        }

        if ($data['type'] === CommonM\AdjustmentTypes::TYPE_DISCOUNT) {
            if ($data['mode'] === CommonM\AdjustmentModes::MODE_PERCENT) {
                return "Discount {$data['amount']}%";
            }

            if (array_key_exists('order', $data) && $data['mode'] === CommonM\AdjustmentModes::MODE_FLAT) {
                return "Discount -{$data['amount']}";
            }

            throw new InvalidArgumentException('Unexpected adjustment mode.');
        }

        throw new InvalidArgumentException('Unexpected adjustment type.');
    }

    /**
     * Fills the given address.
     *
     * @psalm-param array{
     *     company:     string,
     *     street:      string,
     *     complement:  string,
     *     supplement:  string,
     *     extra:       string,
     *     postal_code: string,
     *     city:        string,
     *     country:     mixed,
     *     state:       mixed,
     *     phone:       string,
     *     mobile:      string,
     * } $data
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
            'phone'       => null,
            'mobile'      => null,
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
        if ($datum = $data['phone']) {
            $address->setPhone(Fixture::phone($datum));
        }
        if ($datum = $data['mobile']) {
            $address->setMobile(Fixture::phone($datum));
        }
    }

    private static function decimal(string|int|float $number): Decimal
    {
        return new Decimal((string)$number);
    }

    /**
     * @return array{object, bool}
     */
    private static function create(object|array|int|string $data, string $class): array
    {
        if ($data instanceof $class) {
            return [$data, true];
        } elseif (is_object($data)) {
            throw new UnexpectedTypeException($data, $class);
        }

        if (!empty($data) && (is_int($data) || is_string($data))) {
            return [self::get($data, $class), true];
        }

        if (!is_array($data)) {
            throw new InvalidArgumentException('Expected object, array, int or string');
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
                $r = new ReflectionClass($class);
                $p = $r->getProperty('id');
                /** @noinspection PhpExpressionResultUnusedInspection */
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
     */
    public static function get(int|string $id, string $class = null): object
    {
        if (is_int($id)) {
            if (empty($class)) {
                throw new LogicException('You must provide a class to find a fixture by its id.');
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
            } catch (Throwable) {
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
