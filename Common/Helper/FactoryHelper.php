<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Common\Helper;

use Ekyna\Component\Commerce\Cart\Model as Cart;
use Ekyna\Component\Commerce\Common\Model;
use Ekyna\Component\Commerce\Common\Util\AddressUtil;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Exception\UnexpectedTypeException;
use Ekyna\Component\Commerce\Invoice\Model as Invoice;
use Ekyna\Component\Commerce\Order\Model as Order;
use Ekyna\Component\Commerce\Payment\Model as Payment;
use Ekyna\Component\Commerce\Quote\Model as Quote;
use Ekyna\Component\Commerce\Shipment\Model as Shipment;
use Ekyna\Component\Commerce\Stock\Model as Stock;
use Ekyna\Component\Resource\Factory\FactoryFactoryInterface;

/**
 * Class FactoryHelper
 * @package Ekyna\Component\Commerce\Common
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class FactoryHelper implements FactoryHelperInterface
{
    public const ADDRESS               = 'address';
    public const ADJUSTMENT            = 'adjustment';
    public const ATTACHMENT            = 'attachment';
    public const INVOICE               = 'invoice';
    public const INVOICE_LINE          = 'invoice_line';
    public const ITEM                  = 'item';
    public const ITEM_ADJUSTMENT       = 'item_adjustment';
    public const ITEM_STOCK_ASSIGNMENT = 'item_stock_assignment';
    public const NOTIFICATION          = 'notification';
    public const PAYMENT               = 'payment';
    public const SHIPMENT              = 'shipment';
    public const SHIPMENT_ITEM         = 'shipment_item';

    private const MAP = [
        self::ADDRESS               => [
            Cart\CartInterface::class   => Cart\CartAddressInterface::class,
            Order\OrderInterface::class => Order\OrderAddressInterface::class,
            Quote\QuoteInterface::class => Quote\QuoteAddressInterface::class,
        ],
        self::ADJUSTMENT            => [
            Cart\CartInterface::class   => Cart\CartAdjustmentInterface::class,
            Order\OrderInterface::class => Order\OrderAdjustmentInterface::class,
            Quote\QuoteInterface::class => Quote\QuoteAdjustmentInterface::class,
        ],
        self::ATTACHMENT            => [
            Cart\CartInterface::class   => Cart\CartAttachmentInterface::class,
            Order\OrderInterface::class => Order\OrderAttachmentInterface::class,
            Quote\QuoteInterface::class => Quote\QuoteAttachmentInterface::class,
        ],
        self::INVOICE               => [
            Order\OrderInterface::class => Order\OrderInvoiceInterface::class,
        ],
        self::INVOICE_LINE          => [
            Order\OrderInvoiceInterface::class => Order\OrderInvoiceLineInterface::class,
        ],
        self::ITEM                  => [
            Cart\CartInterface::class   => Cart\CartItemInterface::class,
            Order\OrderInterface::class => Order\OrderItemInterface::class,
            Quote\QuoteInterface::class => Quote\QuoteItemInterface::class,
        ],
        self::ITEM_ADJUSTMENT       => [
            Cart\CartItemInterface::class   => Cart\CartItemAdjustmentInterface::class,
            Order\OrderItemInterface::class => Order\OrderItemAdjustmentInterface::class,
            Quote\QuoteItemInterface::class => Quote\QuoteItemAdjustmentInterface::class,
        ],
        self::ITEM_STOCK_ASSIGNMENT => [
            Order\OrderItemInterface::class => Order\OrderItemStockAssignmentInterface::class,
        ],
        self::NOTIFICATION          => [
            Cart\CartInterface::class   => Cart\CartNotificationInterface::class,
            Order\OrderInterface::class => Order\OrderNotificationInterface::class,
            Quote\QuoteInterface::class => Quote\QuoteNotificationInterface::class,
        ],
        self::PAYMENT               => [
            Cart\CartInterface::class   => Cart\CartPaymentInterface::class,
            Order\OrderInterface::class => Order\OrderPaymentInterface::class,
            Quote\QuoteInterface::class => Quote\QuotePaymentInterface::class,
        ],
        self::SHIPMENT              => [
            Order\OrderInterface::class => Order\OrderShipmentInterface::class,
        ],
        self::SHIPMENT_ITEM         => [
            Order\OrderShipmentInterface::class => Order\OrderShipmentItemInterface::class,
        ],
    ];

    private FactoryFactoryInterface $factory;

    public function __construct(FactoryFactoryInterface $factory)
    {
        $this->factory = $factory;
    }

    public function createAddressForSale(
        Model\SaleInterface     $sale,
        ?Model\AddressInterface $source
    ): Model\SaleAddressInterface {
        /** @var Model\SaleAddressInterface $address */
        $address = $this->resolveClassAndCreateObject(self::ADDRESS, $sale);

        if (null !== $source) {
            AddressUtil::copy($source, $address);
        }

        return $address;
    }

    public function createAdjustmentFor(Model\AdjustableInterface $adjustable): Model\AdjustmentInterface
    {
        if ($adjustable instanceof Model\SaleInterface) {
            return $this->createAdjustmentForSale($adjustable);
        } elseif ($adjustable instanceof Model\SaleItemInterface) {
            return $this->createAdjustmentForItem($adjustable);
        }

        throw new UnexpectedTypeException($adjustable, [
            Model\SaleInterface::class,
            Model\SaleItemInterface::class,
        ]);
    }

    public function createAttachmentForSale(Model\SaleInterface $sale): Model\SaleAttachmentInterface
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->resolveClassAndCreateObject(self::ATTACHMENT, $sale);
    }

    public function createNotificationForSale(Model\SaleInterface $sale): Model\SaleNotificationInterface
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->resolveClassAndCreateObject(self::NOTIFICATION, $sale);
    }

    public function createAdjustmentForItem(Model\SaleItemInterface $item): Model\AdjustmentInterface
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->resolveClassAndCreateObject(self::ITEM_ADJUSTMENT, $item);
    }

    public function createStockAssignmentForItem(Model\SaleItemInterface $item): Stock\StockAssignmentInterface
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->resolveClassAndCreateObject(self::ITEM_STOCK_ASSIGNMENT, $item);
    }

    public function createAdjustmentForSale(Model\SaleInterface $sale): Model\AdjustmentInterface
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->resolveClassAndCreateObject(self::ADJUSTMENT, $sale);
    }

    public function createItemForSale(Model\SaleInterface $sale): Model\SaleItemInterface
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->resolveClassAndCreateObject(self::ITEM, $sale);
    }

    public function createItemForShipment(Shipment\ShipmentInterface $shipment): Shipment\ShipmentItemInterface
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->resolveClassAndCreateObject(self::SHIPMENT_ITEM, $shipment);
    }

    public function createLineForInvoice(Invoice\InvoiceInterface $invoice): Invoice\InvoiceLineInterface
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->resolveClassAndCreateObject(self::INVOICE_LINE, $invoice);
    }

    public function createPaymentForSale(Model\SaleInterface $sale): Payment\PaymentInterface
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->resolveClassAndCreateObject(self::PAYMENT, $sale);
    }

    public function createShipmentForSale(Model\SaleInterface $sale): Shipment\ShipmentInterface
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->resolveClassAndCreateObject(self::SHIPMENT, $sale);
    }

    public function createInvoiceForSale(Model\SaleInterface $sale): Invoice\InvoiceInterface
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->resolveClassAndCreateObject(self::INVOICE, $sale);
    }

    /**
     * Resolves the class and creates the expected object.
     */
    private function resolveClassAndCreateObject(string $type, object $subject): object
    {
        foreach (self::MAP[$type] as $source => $target) {
            if ($subject instanceof $source) {
                return $this->factory->getFactory($target)->create();
            }
        }

        throw new InvalidArgumentException('Unsupported object class.');
    }
}
