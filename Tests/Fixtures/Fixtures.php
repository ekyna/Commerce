<?php

namespace Ekyna\Component\Commerce\Tests\Fixtures;

use Acme\Product\Entity as Acme;
use Ekyna\Component\Commerce\Common\Entity as CommonE;
use Ekyna\Component\Commerce\Common\Model as CommonM;
use Ekyna\Component\Commerce\Customer\Entity as CustomerE;
use Ekyna\Component\Commerce\Customer\Model as CustomerM;
use Ekyna\Component\Commerce\Document\Model\DocumentLineTypes;
use Ekyna\Component\Commerce\Exception\UnexpectedTypeException;
use Ekyna\Component\Commerce\Invoice\Model as InvoiceM;
use Ekyna\Component\Commerce\Order\Entity as OrderE;
use Ekyna\Component\Commerce\Order\Model as OrderM;
use Ekyna\Component\Commerce\Payment\Model\PaymentStates;
use Ekyna\Component\Commerce\Pricing\Entity as PricingE;
use Ekyna\Component\Commerce\Pricing\Model as PricingM;
use Ekyna\Component\Commerce\Shipment\Model as ShipmentM;
use Ekyna\Component\Commerce\Stock\Entity\StockAdjustment;
use Ekyna\Component\Commerce\Stock\Model as StockM;
use Ekyna\Component\Commerce\Stock\Resolver\StockUnitStateResolver;
use Ekyna\Component\Commerce\Stock\Resolver\StockUnitStateResolverInterface;
use Ekyna\Component\Commerce\Supplier\Entity as SupplierE;
use Symfony\Component\Intl\Intl;
use Symfony\Component\Yaml\Yaml;

/**
 * Class Fixtures
 * @package Ekyna\Component\Commerce\Tests\Fixtures
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class Fixtures
{
    private const DATA_DIR   = __DIR__ . '/../../Install/data/';
    private const COUNTRIES  = ['FR', 'US'];
    private const CURRENCIES = ['EUR', 'USD'];

    /**
     * @var CustomerM\CustomerGroupInterface[]
     */
    private static $customerGroups;

    /**
     * @var CommonM\CurrencyInterface[]
     */
    private static $currencies;

    /**
     * @var CommonM\CountryInterface[]
     */
    private static $countries;

    /**
     * @var PricingM\TaxGroupInterface[]
     */
    private static $taxGroups;

    /**
     * @var StockUnitStateResolverInterface
     */
    private static $stockUnitStateResolver;


    /**
     * Resolves the stock unit state.
     *
     * @param StockM\StockUnitInterface $stockUnit
     */
    public static function resolveStockUnitState(StockM\StockUnitInterface $stockUnit): void
    {
        if (null === static::$stockUnitStateResolver) {
            static::$stockUnitStateResolver = new StockUnitStateResolver();
        }

        static::$stockUnitStateResolver->resolve($stockUnit);
    }

    /**
     * Returns the customer groups.
     *
     * @return array|CustomerM\CustomerGroupInterface[]
     */
    public static function getCustomerGroups(): array
    {
        if (null !== static::$customerGroups) {
            return static::$customerGroups;
        }

        $customers = new CustomerE\CustomerGroup();
        $customers
            ->setName('Customers')
            ->setDefault(true);

        $resellers = new CustomerE\CustomerGroup();
        $resellers
            ->setName('Resellers')
            ->setDefault(false);

        return static::$customerGroups = [$customers, $resellers];
    }

    /**
     * Returns the default customer group.
     *
     * @return CustomerM\CustomerGroupInterface
     */
    public static function getDefaultCustomerGroup(): CustomerM\CustomerGroupInterface
    {
        return static::getCustomerGroups()[0];
    }

    /**
     * Returns the currencies.
     *
     * @return CommonM\CurrencyInterface[]
     */
    public static function getCurrencies(): array
    {
        if (null !== static::$currencies) {
            return static::$currencies;
        }

        static::$currencies = [];

        foreach (static::CURRENCIES as $code) {
            $currency = new CommonE\Currency();
            $currency
                ->setName(Intl::getCurrencyBundle()->getCurrencyName($code))
                ->setCode($code)
                ->setEnabled(true);

            static::$currencies[] = $currency;
        }

        return static::$currencies;
    }

    /**
     * Returns the default currency.
     *
     * @return CommonM\CurrencyInterface
     */
    public static function getDefaultCurrency(): CommonM\CurrencyInterface
    {
        return static::getCurrencies()[0];
    }

    /**
     * Finds the currency by its code.
     *
     * @param string $code
     *
     * @return CommonM\CurrencyInterface
     */
    public static function getCurrencyByCode(string $code): CommonM\CurrencyInterface
    {
        foreach (static::getCurrencies() as $currency) {
            if ($currency->getCode() === $code) {
                return $currency;
            }
        }

        throw new \InvalidArgumentException("Unexpected currency code '$code'.");
    }

    /**
     * Returns the countries.
     *
     * @return CommonM\CountryInterface[]
     */
    public static function getCountries(): array
    {
        if (null !== static::$countries) {
            return static::$countries;
        }

        static::$countries = [];

        foreach (static::COUNTRIES as $code) {
            $country = new CommonE\Country();
            $country
                ->setName(Intl::getRegionBundle()->getCountryName($code))
                ->setCode($code)
                ->setEnabled(true);

            static::$countries[] = $country;
        }

        return static::$countries;
    }

    /**
     * Returns the default country.
     *
     * @return CommonM\CountryInterface
     */
    public static function getDefaultCountry(): CommonM\CountryInterface
    {
        return static::getCountries()[0];
    }

    /**
     * Finds the country by its code.
     *
     * @param string $code
     *
     * @return CommonM\CountryInterface
     */
    public static function getCountryByCode(string $code): CommonM\CountryInterface
    {
        foreach (static::getCountries() as $country) {
            if ($country->getCode() === $code) {
                return $country;
            }
        }

        throw new \InvalidArgumentException("Unexpected country code '$code'.");
    }

    /**
     * Returns the tax groups.
     *
     * @return PricingM\TaxGroupInterface[]
     */
    public static function getTaxGroups(): array
    {
        if (null !== static::$taxGroups) {
            return static::$taxGroups;
        }

        static::$taxGroups = [];

        foreach (static::COUNTRIES as $code) {
            $path = static::DATA_DIR . $code . '_tax_groups.yml';
            if (!(file_exists($path) && is_readable($path))) {
                continue;
                //throw new \RuntimeException("Can't read $path file.");
            }

            $data = Yaml::parse(file_get_contents($path));
            if (!is_array($data) || empty($data)) {
                throw new \RuntimeException("File $path is invalid or empty.");
            }

            foreach ($data as $datum) {
                $taxGroup = new PricingE\TaxGroup();
                $taxGroup
                    ->setName($datum['name'])
                    ->setDefault($datum['default']);

                static::$taxGroups[] = $taxGroup;
            }
        }

        return static::$taxGroups;
    }

    /**
     * Returns the default tax group.
     *
     * @return PricingM\TaxGroupInterface
     */
    public static function getDefaultTaxGroup(): PricingM\TaxGroupInterface
    {
        foreach (static::getTaxGroups() as $group) {
            if ($group->isDefault()) {
                return $group;
            }
        }

        throw new \RuntimeException("Default tax group not found.");
    }

    /**
     * Finds the country by its code.
     *
     * @param string $name
     *
     * @return PricingM\TaxGroupInterface
     */
    public static function getTaxGroupByName(string $name): PricingM\TaxGroupInterface
    {
        foreach (static::getTaxGroups() as $group) {
            if ($group->getName() === $name) {
                return $group;
            }
        }

        throw new \RuntimeException("Tax group '$name' not found.");
    }

    /**
     * Creates a customer.
     *
     * @return CustomerM\CustomerInterface
     */
    public static function createCustomer(): CustomerM\CustomerInterface
    {
        $customer = new CustomerE\Customer();

        $customer
            ->setCustomerGroup(self::getDefaultCustomerGroup())
            ->setCompany('Acme')
            ->setFirstName('John')
            ->setLastName('Doe')
            ->setEmail('john.doe@acme.com');

        return $customer;
    }

    /**
     * Creates a stock assignment.
     *
     * @param StockM\StockUnitInterface $unit
     * @param OrderM\OrderItemInterface $item
     * @param int                       $sold
     * @param int                       $shipped
     *
     * @return OrderE\OrderItemStockAssignment
     */
    public static function createStockAssignment(
        StockM\StockUnitInterface $unit = null,
        OrderM\OrderItemInterface $item = null,
        int $sold = 0,
        int $shipped = 0
    ): OrderE\OrderItemStockAssignment {
        $assignment = new OrderE\OrderItemStockAssignment();

        $assignment
            ->setStockUnit($unit)
            ->setSaleItem($item)
            ->setSoldQuantity($sold)
            ->setShippedQuantity($shipped);

        return $assignment;
    }

    /**
     * Creates a new stock unit.
     *
     * @param StockM\StockSubjectInterface $subject
     * @param SupplierE\SupplierOrderItem  $item
     * @param float                        $ordered
     * @param float                        $received
     * @param float                        $sold
     * @param float                        $shipped
     *
     * @return Acme\StockUnit
     */
    public static function createStockUnit(
        StockM\StockSubjectInterface $subject = null,
        SupplierE\SupplierOrderItem $item = null,
        float $ordered = .0,
        float $received = .0,
        float $sold = .0,
        float $shipped = .0
    ): StockM\StockUnitInterface {
        $unit = new Acme\StockUnit();

        if (null === $subject && $item && null === $subject = $item->getSubjectIdentity()->getSubject()) {
            $subject = static::createSubject();
        }

        if ($subject) {
            $unit->setSubject($subject);
        }
        if ($item) {
            $unit->setSupplierOrderItem($item);
        }

        $unit
            ->setOrderedQuantity($ordered)
            ->setReceivedQuantity($received)
            ->setSoldQuantity($sold)
            ->setShippedQuantity($shipped);

        static::resolveStockUnitState($unit);

        return $unit;
    }

    /**
     * Creates a new stock adjustment.
     *
     * @param StockM\StockUnitInterface $unit
     * @param float                     $quantity
     * @param bool                      $debit
     *
     * @return StockAdjustment
     */
    public static function createStockAdjustment(
        StockM\StockUnitInterface $unit,
        float $quantity,
        bool $debit = false
    ): StockM\StockAdjustmentInterface {
        $adjustment = new StockAdjustment();
        $adjustment
            ->setStockUnit($unit)
            ->setQuantity($quantity)
            ->setReason($debit ? StockM\StockAdjustmentReasons::REASON_DEBIT : StockM\StockAdjustmentReasons::REASON_CREDIT);

        $adjusted = $unit->getAdjustedQuantity();
        $adjusted += $debit ? -$quantity : $quantity;
        $unit->setAdjustedQuantity($adjusted);

        return $adjustment;
    }

    /**
     * Creates a new subject (acme product).
     *
     * @param string $designation
     * @param string $reference
     * @param float  $price
     * @param float  $weight
     *
     * @return Acme\Product
     */
    public static function createSubject(
        string $designation = null,
        string $reference = null,
        float $price = null,
        float $weight = null
    ): Acme\Product {
        $subject = new Acme\Product();

        if (empty($designation)) {
            $designation = 'Apple iPhone';
        }
        if (empty($reference)) {
            $reference = 'APPL-IPHO';
        }
        if (empty($price)) {
            $price = 249.0;
        }
        if (empty($weight)) {
            $weight = 0.8;
        }

        $rc = new \ReflectionProperty(Acme\Product::class, 'id');
        $rc->setAccessible(true);
        $rc->setValue($subject, rand(1, 9999)); // TODO Oops

        $subject
            ->setDesignation($designation)
            ->setReference($reference)
            ->setNetPrice($price)
            ->setPackageWeight($weight);

        return $subject;
    }

    /**
     * Creates a new supplier order.
     *
     * @param string $currency
     * @param string $createdAt
     * @param string $orderedAt
     *
     * @return SupplierE\SupplierOrder
     */
    public static function createSupplierOrder(string $currency = null, string $createdAt = null, string $orderedAt = null): SupplierE\SupplierOrder
    {
        $order = new SupplierE\SupplierOrder();
        $order
            ->setCurrency(static::getCurrencyByCode($currency ?? 'EUR'))
            ->setCreatedAt(new \DateTime($createdAt ?? 'now'));

        if ($orderedAt) {
            $order->setOrderedAt(new \DateTime($orderedAt));
        }

        return $order;
    }

    /**
     * Creates a new supplier order item.
     *
     * @param StockM\StockSubjectInterface $subject
     * @param float                        $quantity
     * @param float                        $netPrice
     *
     * @return SupplierE\SupplierOrderItem
     */
    public static function createSupplierOrderItem(
        StockM\StockSubjectInterface $subject = null,
        float $quantity = 1.,
        float $netPrice = .0
    ): SupplierE\SupplierOrderItem {
        $item = new SupplierE\SupplierOrderItem();

        if (null === $subject) {
            $subject = static::createSubject();
        }

        if ($subject) {
            $item
                ->getSubjectIdentity()
                ->setSubject($subject)
                ->setProvider($subject::getProviderName())
                ->setIdentifier($subject->getIdentifier());
        }

        $item
            ->setQuantity($quantity)
            ->setNetPrice($netPrice);

        return $item;
    }

    /**
     * Creates a payment.
     *
     * @param string $currency
     * @param float  $amount
     * @param string $state
     *
     * @return OrderE\OrderPayment
     */
    public static function createPayment(
        string $currency = 'EUR',
        float $amount = 100,
        string $state = PaymentStates::STATE_CAPTURED
    ): OrderE\OrderPayment {
        $payment = new OrderE\OrderPayment();
        $payment
            ->setCurrency(static::getCurrencyByCode($currency))
            ->setAmount($amount)
            ->setRealAmount($amount)
            ->setState($state);

        return $payment;
    }

    /**
     * Creates a new order.
     *
     * @param string $currency
     * @param string $createdAt
     *
     * @return OrderE\Order
     */
    public static function createOrder(string $currency = 'EUR', string $createdAt = 'now'): OrderE\Order
    {
        $order = new OrderE\Order();

        $order
            ->setCurrency(static::getCurrencyByCode($currency))
            ->setCreatedAt(new \DateTime($createdAt));

        return $order;
    }

    /**
     * Creates a new order item.
     *
     * @param float $quantity
     * @param float $netPrice
     * @param array $discounts
     * @param array $taxes
     *
     * @return OrderE\OrderItem
     */
    public static function createOrderItem(float $quantity = 1., float $netPrice = .0, array $discounts = [], array $taxes = []): OrderE\OrderItem
    {
        $item = new OrderE\OrderItem();
        $item
            ->setQuantity($quantity)
            ->setNetPrice($netPrice);

        foreach ($discounts as $rate) {
            $item->addAdjustment(Fixtures::createOrderItemDiscountAdjustment($rate));
        }

        foreach ($taxes as $rate) {
            $item->addAdjustment(Fixtures::createOrderItemTaxationAdjustment($rate));
        }

        return $item;
    }

    /**
     * Creates a new order item taxation adjustment.
     *
     * @param float $amount
     *
     * @return OrderE\OrderItemAdjustment
     */
    public static function createOrderItemTaxationAdjustment(float $amount): OrderE\OrderItemAdjustment
    {
        $adjustment = new OrderE\OrderItemAdjustment();
        $adjustment
            ->setType(CommonM\AdjustmentTypes::TYPE_TAXATION)
            ->setMode(CommonM\AdjustmentModes::MODE_PERCENT)
            ->setDesignation("VAT $amount%")
            ->setAmount($amount);

        return $adjustment;
    }

    /**
     * Creates a new order item taxation adjustment.
     *
     * @param float $amount
     *
     * @return OrderE\OrderItemAdjustment
     */
    public static function createOrderItemDiscountAdjustment(float $amount): OrderE\OrderItemAdjustment
    {
        $adjustment = new OrderE\OrderItemAdjustment();
        $adjustment
            ->setType(CommonM\AdjustmentTypes::TYPE_DISCOUNT)
            ->setMode(CommonM\AdjustmentModes::MODE_PERCENT)
            ->setDesignation("Discount $amount%")
            ->setAmount($amount);

        return $adjustment;
    }

    /**
     * Creates a new order taxation adjustment (for shipment).
     *
     * @param float $amount
     *
     * @return OrderE\OrderAdjustment
     */
    public static function createOrderTaxationAdjustment(float $amount): OrderE\OrderAdjustment
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
     */
    public static function createOrderDiscountAdjustment(float $amount, bool $flat = false): OrderE\OrderAdjustment
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
     * @param OrderM\OrderInterface $order
     *
     * @return OrderE\OrderShipment
     */
    public static function createShipment(OrderM\OrderInterface $order): OrderE\OrderShipment
    {
        $shipment = new OrderE\OrderShipment();
        $shipment->setOrder($order);

        return $shipment;
    }

    /**
     * Creates a new shipment item.
     *
     * @param ShipmentM\ShipmentInterface $shipment
     * @param OrderM\OrderItemInterface   $orderItem
     *
     * @return OrderE\OrderShipmentItem
     */
    public static function createShipmentItem(
        ShipmentM\ShipmentInterface $shipment,
        OrderM\OrderItemInterface $orderItem
    ): OrderE\OrderShipmentItem {
        $item = new OrderE\OrderShipmentItem();
        $item
            ->setShipment($shipment)
            ->setOrderItem($orderItem);

        return $item;
    }

    /**
     * Creates a new invoice.
     *
     * @param OrderM\OrderInterface $order
     * @param bool                  $credit
     *
     * @return OrderE\OrderInvoice
     */
    public static function createInvoice(OrderM\OrderInterface $order, bool $credit = false): OrderE\OrderInvoice
    {
        $invoice = new OrderE\OrderInvoice();
        $invoice
            ->setOrder($order)
            ->setCredit($credit);

        return $invoice;
    }

    /**
     * Creates a new invoice item.
     *
     * @param InvoiceM\InvoiceInterface $invoice
     * @param object                    $target
     *
     * @return OrderE\OrderInvoiceLine
     */
    public static function createInvoiceLine(InvoiceM\InvoiceInterface $invoice, object $target): OrderE\OrderInvoiceLine
    {
        $line = new OrderE\OrderInvoiceLine();
        $line->setInvoice($invoice);

        if ($target instanceof OrderM\OrderItemInterface) {
            $line
                ->setOrderItem($target)
                ->setType(DocumentLineTypes::TYPE_GOOD);
        } elseif ($target instanceof OrderM\OrderAdjustmentInterface) {
            $line
                ->setOrderAdjustment($target)
                ->setType(DocumentLineTypes::TYPE_DISCOUNT);
        } elseif ($target instanceof OrderM\OrderInterface) {
            $line->setType(DocumentLineTypes::TYPE_SHIPMENT);
        } else {
            throw new UnexpectedTypeException($target, [
                OrderM\OrderItemInterface::class,
                OrderM\OrderAdjustmentInterface::class,
            ]);
        }

        return $line;
    }

    /**
     * Sets the object id.
     *
     * @param object $object
     * @param int    $id
     *
     * @throws \ReflectionException
     */
    public static function setId(object $object, int $id): void
    {
        $r = new \ReflectionClass(get_class($object));
        $p = $r->getProperty('id');
        $p->setAccessible(true);
        $p->setValue($object, $id);
        $p->setAccessible(false);
    }
}
