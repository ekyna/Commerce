<?php

namespace Ekyna\Component\Commerce\Tests\Fixtures;

use Acme\Product\Entity as Acme;
use Ekyna\Component\Commerce\Common\Entity as CommonE;
use Ekyna\Component\Commerce\Common\Model as CommonM;
use Ekyna\Component\Commerce\Customer\Entity as CustomerE;
use Ekyna\Component\Commerce\Customer\Model as CustomerM;
use Ekyna\Component\Commerce\Order\Entity as OrderE;
use Ekyna\Component\Commerce\Order\Model as OrderM;
use Ekyna\Component\Commerce\Pricing\Entity as PricingE;
use Ekyna\Component\Commerce\Pricing\Model as PricingM;
use Ekyna\Component\Commerce\Shipment\Model as ShipmentM;
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
    const DATA_DIR   = __DIR__ . '/../../Install/data/';
    const COUNTRIES  = ['FR', 'US'];
    const CURRENCIES = ['EUR', 'USD'];

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
    private static function resolveStockUnitState(StockM\StockUnitInterface $stockUnit)
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
    public static function getCustomerGroups()
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
    public static function getDefaultCustomerGroup()
    {
        return static::getCustomerGroups()[0];
    }

    /**
     * Returns the currencies.
     *
     * @return CommonM\CurrencyInterface[]
     */
    public static function getCurrencies()
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
    public static function getDefaultCurrency()
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
    public static function getCurrencyByCode($code)
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
    public static function getCountries()
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
    public static function getDefaultCountry()
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
    public static function getCountryByCode($code)
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
    public static function getTaxGroups()
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
    public static function getDefaultTaxGroup()
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
    public static function getTaxGroupByName($name)
    {
        foreach (static::getTaxGroups() as $group) {
            if ($group->getName() === $name) {
                return $group;
            }
        }

        throw new \RuntimeException("Tax group '$name' not found.");
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
    public static function createStockAssignment($unit = null, $item = null, $sold = 0, $shipped = 0)
    {
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
    public static function createStockUnit($subject = null, $item = null, $ordered = .0, $received = .0, $sold = .0, $shipped = .0)
    {
        $unit = new Acme\StockUnit();

        if ($subject) $unit->setSubject($subject);
        if ($item) $unit->setSupplierOrderItem($item);

        $unit
            ->setOrderedQuantity($ordered)
            ->setReceivedQuantity($received)
            ->setSoldQuantity($sold)
            ->setShippedQuantity($shipped);

        static::resolveStockUnitState($unit);

        return $unit;
    }

    /**
     * Creates a new product (stock subject).
     *
     * @return Acme\Product
     */
    public static function createProduct()
    {
        $subject = new Acme\Product();

        $subject
            ->setDesignation('Apple iPhone')
            ->setReference('APPL-IPHO')
            ->setNetPrice(249.0)
            ->setWeight(0.8);

        return $subject;
    }

    /**
     * Creates a new supplier order.
     *
     * @param string $currency
     * @param string $createAt
     * @param string $orderedAt
     *
     * @return SupplierE\SupplierOrder
     */
    public static function createSupplierOrder($currency = 'EUR', $createAt = 'now', $orderedAt = null)
    {
        $order = new SupplierE\SupplierOrder();
        $order
            ->setCurrency(static::getCurrencyByCode($currency))
            ->setCreatedAt(new \DateTime($createAt));

        if ($orderedAt) {
            $order->setOrderedAt(new \DateTime($orderedAt));
        }

        return $order;
    }

    /**
     * Creates a new supplier order item.
     *
     * @param float $quantity
     * @param float $netPrice
     *
     * @return SupplierE\SupplierOrderItem
     */
    public static function createSupplierOrderItem($quantity = 1., $netPrice = .0)
    {
        $item = new SupplierE\SupplierOrderItem();
        $item
            ->setQuantity($quantity)
            ->setNetPrice($netPrice);

        return $item;
    }

    /**
     * Creates a new order.
     *
     * @param string $currency
     * @param string $createdAt
     *
     * @return OrderE\Order
     */
    public static function createOrder($currency = 'EUR', $createdAt = 'now')
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
    public static function createOrderItem($quantity = 1., $netPrice = .0, array $discounts = [], array $taxes = [])
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
    public static function createOrderItemTaxationAdjustment($amount)
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
    public static function createOrderItemDiscountAdjustment($amount)
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
     * Creates a new order taxation adjustment.
     *
     * @param float $amount
     *
     * @return OrderE\OrderAdjustment
     */
    public static function createOrderTaxationAdjustment($amount)
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
    public static function createOrderDiscountAdjustment($amount, $flat = false)
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
    public static function createShipment(OrderM\OrderInterface $order)
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
    public static function createShipmentItem(ShipmentM\ShipmentInterface $shipment, OrderM\OrderItemInterface $orderItem)
    {
        $item = new OrderE\OrderShipmentItem();
        $item
            ->setShipment($shipment)
            ->setOrderItem($orderItem);

        return $item;
    }
}
