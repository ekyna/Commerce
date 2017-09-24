<?php

namespace Ekyna\Component\Commerce\Common\Factory;

use Ekyna\Component\Commerce\Cart;
use Ekyna\Component\Commerce\Common\Model;
use Ekyna\Component\Commerce\Common\Repository\CurrencyRepositoryInterface;
use Ekyna\Component\Commerce\Common\Util\AddressUtil;
use Ekyna\Component\Commerce\Invoice\Model\InvoiceInterface;
use Ekyna\Component\Commerce\Customer\Repository\CustomerGroupRepositoryInterface;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Order;
use Ekyna\Component\Commerce\Quote;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentInterface;

/**
 * Class SaleFactory
 * @package Ekyna\Component\Commerce\Common
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SaleFactory implements SaleFactoryInterface
{
    /**
     * @var CustomerGroupRepositoryInterface
     */
    private $customerGroupRepository;

    /**
     * @var CurrencyRepositoryInterface
     */
    private $currencyRepository;

    /**
     * @var array
     */
    private $classes;


    /**
     * Constructor.
     *
     * @param CustomerGroupRepositoryInterface $customerGroupRepository
     * @param CurrencyRepositoryInterface      $currencyRepository
     * @param array                            $classes
     */
    public function __construct(
        CustomerGroupRepositoryInterface $customerGroupRepository,
        CurrencyRepositoryInterface $currencyRepository,
        array $classes = []
    ) {
        $this->customerGroupRepository = $customerGroupRepository;
        $this->currencyRepository = $currencyRepository;

        $this->classes = array_replace_recursive($this->getDefaultClasses(), $classes);

        // TODO validate classes
    }

    /**
     * @inheritdoc
     */
    public function getDefaultCustomerGroup()
    {
        return $this->customerGroupRepository->findDefault();
    }

    /**
     * @inheritdoc
     */
    public function getDefaultCurrency()
    {
        return $this->currencyRepository->findDefault();
    }

    /**
     * @inheritdoc
     */
    public function createAddressForSale(Model\SaleInterface $sale, Model\SaleAddressInterface $source = null)
    {
        /** @var Model\SaleAddressInterface $address */
        $address = $this->resolveClassAndCreateObject('address', $sale);

        if (null !== $source) {
            AddressUtil::copy($source, $address);
        }

        return $address;
    }

    /**
     * @inheritdoc
     */
    public function createAttachmentForSale(Model\SaleInterface $sale)
    {
        return $this->resolveClassAndCreateObject('attachment', $sale);
    }

    /**
     * @inheritdoc
     */
    public function createAdjustmentFor(Model\AdjustableInterface $adjustable)
    {
        if ($adjustable instanceof Model\SaleInterface) {
            return $this->createAdjustmentForSale($adjustable);
        } elseif ($adjustable instanceof Model\SaleItemInterface) {
            return $this->createAdjustmentForItem($adjustable);
        }

        throw new InvalidArgumentException("Expected instanceof SaleInterface or SaleItemInterface.");
    }

    /**
     * @inheritdoc
     */
    public function createAdjustmentForItem(Model\SaleItemInterface $item)
    {
        return $this->resolveClassAndCreateObject('item_adjustment', $item);
    }

    /**
     * @inheritdoc
     */
    public function createStockAssignmentForItem(Model\SaleItemInterface $item)
    {
        return $this->resolveClassAndCreateObject('item_stock_assignment', $item);
    }

    /**
     * @inheritdoc
     */
    public function createAdjustmentForSale(Model\SaleInterface $sale)
    {
        return $this->resolveClassAndCreateObject('adjustment', $sale);
    }

    /**
     * @inheritdoc
     */
    public function createItemForSale(Model\SaleInterface $sale)
    {
        return $this->resolveClassAndCreateObject('item', $sale);
    }

    /**
     * @inheritdoc
     */
    public function createItemForShipment(ShipmentInterface $shipment)
    {
        return $this->resolveClassAndCreateObject('shipment_item', $shipment);
    }

    /**
     * @inheritdoc
     */
    public function createLineForInvoice(InvoiceInterface $invoice)
    {
        return $this->resolveClassAndCreateObject('invoice_line', $invoice);
    }

    /**
     * @inheritdoc
     */
    public function createPaymentForSale(Model\SaleInterface $sale)
    {
        /** @var \Ekyna\Component\Commerce\Payment\Model\PaymentInterface $payment */
        $payment = $this->resolveClassAndCreateObject('payment', $sale);

        $payment
            ->setCurrency($sale->getCurrency())
            ->setAmount($sale->getGrandTotal() - $sale->getPaidTotal());

        return $payment;
    }

    /**
     * @inheritdoc
     */
    public function createShipmentForSale(Model\SaleInterface $sale)
    {
        return $this->resolveClassAndCreateObject('shipment', $sale);
    }

    /**
     * @inheritdoc
     */
    public function createInvoiceForSale(Model\SaleInterface $sale)
    {
        return $this->resolveClassAndCreateObject('invoice', $sale);
    }

    /**
     * Resolves the class and creates the expected object.
     *
     * @param string $type
     * @param object $subject
     *
     * @return object
     */
    private function resolveClassAndCreateObject($type, $subject)
    {
        foreach ($this->classes[$type] as $source => $target) {
            if ($subject instanceof $source) {
                return new $target;
            }
        }

        throw new InvalidArgumentException('Unsupported object class.');
    }

    /**
     * Returns the default classes.
     *
     * @return array
     */
    private function getDefaultClasses()
    {
        // TODO use constants for keys

        return [
            'address'               => [
                Cart\Model\CartInterface::class   => Cart\Entity\CartAddress::class,
                Order\Model\OrderInterface::class => Order\Entity\OrderAddress::class,
                Quote\Model\QuoteInterface::class => Quote\Entity\QuoteAddress::class,
            ],
            'attachment'            => [
                Cart\Model\CartInterface::class   => Cart\Entity\CartAttachment::class,
                Order\Model\OrderInterface::class => Order\Entity\OrderAttachment::class,
                Quote\Model\QuoteInterface::class => Quote\Entity\QuoteAttachment::class,
            ],
            'item'                  => [
                Cart\Model\CartInterface::class   => Cart\Entity\CartItem::class,
                Order\Model\OrderInterface::class => Order\Entity\OrderItem::class,
                Quote\Model\QuoteInterface::class => Quote\Entity\QuoteItem::class,
            ],
            'adjustment'            => [
                Cart\Model\CartInterface::class   => Cart\Entity\CartAdjustment::class,
                Order\Model\OrderInterface::class => Order\Entity\OrderAdjustment::class,
                Quote\Model\QuoteInterface::class => Quote\Entity\QuoteAdjustment::class,
            ],
            'item_adjustment'       => [
                Cart\Model\CartItemInterface::class   => Cart\Entity\CartItemAdjustment::class,
                Order\Model\OrderItemInterface::class => Order\Entity\OrderItemAdjustment::class,
                Quote\Model\QuoteItemInterface::class => Quote\Entity\QuoteItemAdjustment::class,
            ],
            'item_stock_assignment' => [
                Order\Model\OrderItemInterface::class => Order\Entity\OrderItemStockAssignment::class,
            ],
            'payment'               => [
                Cart\Model\CartInterface::class   => Cart\Entity\CartPayment::class,
                Order\Model\OrderInterface::class => Order\Entity\OrderPayment::class,
                Quote\Model\QuoteInterface::class => Quote\Entity\QuotePayment::class,
            ],
            'shipment'              => [
                Order\Model\OrderInterface::class => Order\Entity\OrderShipment::class,
            ],
            'shipment_item'         => [
                Order\Model\OrderShipmentInterface::class => Order\Entity\OrderShipmentItem::class,
            ],
            'invoice'               => [
                Order\Model\OrderInterface::class => Order\Entity\OrderInvoice::class,
            ],
            'invoice_line'          => [
                Order\Model\OrderInvoiceInterface::class => Order\Entity\OrderInvoiceLine::class,
            ],
        ];
    }
}
