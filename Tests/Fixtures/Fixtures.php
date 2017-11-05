<?php

namespace Ekyna\Component\Commerce\Tests\Fixtures;

use Acme\Product\Entity as Acme;
use Ekyna\Component\Commerce\Common\Model\CurrencyInterface;
use Ekyna\Component\Commerce\Order\Entity as Order;
use Ekyna\Component\Commerce\Order\Model\OrderItemInterface;
use Ekyna\Component\Commerce\Stock\Model\StockSubjectInterface;
use Ekyna\Component\Commerce\Stock\Model\StockUnitInterface;
use Ekyna\Component\Commerce\Stock\Resolver\StockUnitStateResolver;
use Ekyna\Component\Commerce\Stock\Resolver\StockUnitStateResolverInterface;
use Ekyna\Component\Commerce\Supplier\Entity as Supplier;

/**
 * Class Fixtures
 * @package Ekyna\Component\Commerce\Tests\Fixtures
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class Fixtures
{
    /**
     * @var StockUnitStateResolverInterface
     */
    private static $stockUnitStateResolver;


    /**
     * Resolves the stock unit state.
     *
     * @param StockUnitInterface $stockUnit
     */
    private static function resolveStockUnitState(StockUnitInterface $stockUnit)
    {
        if (null === static::$stockUnitStateResolver) {
            static::$stockUnitStateResolver = new StockUnitStateResolver();
        }

        static::$stockUnitStateResolver->resolve($stockUnit);
    }

    /**
     * Creates a stock assignment.
     *
     * @param StockUnitInterface $unit
     * @param OrderItemInterface $item
     * @param int  $sold
     * @param int  $shipped
     *
     * @return Order\OrderItemStockAssignment
     */
    public static function createStockAssignment($unit = null, $item = null, $sold = 0, $shipped = 0)
    {
        $assignment = new Order\OrderItemStockAssignment();

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
     * @param StockSubjectInterface      $subject
     * @param Supplier\SupplierOrderItem $item
     * @param int                        $ordered
     * @param int                        $received
     * @param int                        $sold
     * @param int                        $shipped
     *
     * @return Acme\StockUnit
     */
    public static function createStockUnit($subject = null, $item = null, $ordered = 0, $received = 0, $sold = 0, $shipped = 0)
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
     * @param CurrencyInterface $currency
     * @param string            $modifyCreatedAt
     *
     * @return Supplier\SupplierOrder
     */
    public static function createSupplierOrder(CurrencyInterface $currency, $modifyCreatedAt = null)
    {
        $createdAt = new \DateTime();
        if ($modifyCreatedAt) {
            $createdAt->modify($modifyCreatedAt);
        }

        $order = new Supplier\SupplierOrder();
        $order
            ->setCurrency($currency)
            ->setCreatedAt($createdAt);

        return $order;
    }

    /**
     * Creates a new supplier order item.
     *
     * @param float $quantity
     * @param float $netPrice
     *
     * @return Supplier\SupplierOrderItem
     */
    public static function createSupplierOrderItem($quantity = 1., $netPrice = .0)
    {
        $item = new Supplier\SupplierOrderItem();
        $item
            ->setQuantity($quantity)
            ->setNetPrice($netPrice);

        return $item;
    }

    /**
     * Creates a new order item.
     *
     * @param float $quantity
     * @param float $netPrice
     *
     * @return Order\OrderItem
     */
    public static function createOrderItem($quantity = 1., $netPrice = .0)
    {
        $item = new Order\OrderItem();
        $item
            ->setQuantity($quantity)
            ->setNetPrice($netPrice);

        return $item;
    }
}
