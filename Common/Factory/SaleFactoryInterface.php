<?php

namespace Ekyna\Component\Commerce\Common\Factory;

use Ekyna\Component\Commerce\Common\Model;
use Ekyna\Component\Commerce\Payment\Model as Payment;
use Ekyna\Component\Commerce\Shipment\Model as Shipment;
use Ekyna\Component\Commerce\Invoice\Model as Invoice;
use Ekyna\Component\Commerce\Stock\Model as Stock;

/**
 * Interface SaleFactoryInterface
 * @package Ekyna\Component\Commerce\Common
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface SaleFactoryInterface
{
    /**
     * Creates an address regarding to the sale type.
     *
     * @param Model\SaleInterface    $sale
     * @param Model\AddressInterface $source
     *
     * @return Model\SaleAddressInterface
     */
    public function createAddressForSale(Model\SaleInterface $sale, Model\AddressInterface $source = null);

    /**
     * Creates an adjustment for the given adjustable.
     *
     * @param Model\AdjustableInterface $adjustable
     *
     * @return Model\AdjustmentInterface
     */
    public function createAdjustmentFor(Model\AdjustableInterface $adjustable);

    /**
     * Creates an attachment regarding to the sale type.
     *
     * @param Model\SaleInterface $sale
     *
     * @return Model\SaleAttachmentInterface
     */
    public function createAttachmentForSale(Model\SaleInterface $sale);

    /**
     * Creates a notification regarding to the sale type.
     *
     * @param Model\SaleInterface $sale
     *
     * @return Model\SaleNotificationInterface
     */
    public function createNotificationForSale(Model\SaleInterface $sale);

    /**
     * Creates an adjustment regarding to the sale item type.
     *
     * @param Model\SaleItemInterface $item
     *
     * @return Model\AdjustmentInterface
     */
    public function createAdjustmentForItem(Model\SaleItemInterface $item);

    /**
     * Creates a stock assignment regarding to the sale item type.
     *
     * @param Model\SaleItemInterface $item
     *
     * @return Stock\StockAssignmentInterface
     */
    public function createStockAssignmentForItem(Model\SaleItemInterface $item);

    /**
     * Creates an adjustment regarding to the sale type.
     *
     * @param Model\SaleInterface $sale
     *
     * @return Model\AdjustmentInterface
     */
    public function createAdjustmentForSale(Model\SaleInterface $sale);

    /**
     * Creates a sale item regarding to the sale type.
     *
     * @param Model\SaleInterface $sale
     *
     * @return Model\SaleItemInterface
     */
    public function createItemForSale(Model\SaleInterface $sale);

    /**
     * Creates a shipment regarding to the sale type.
     *
     * @param Shipment\ShipmentInterface $shipment
     *
     * @return Shipment\ShipmentItemInterface
     */
    public function createItemForShipment(Shipment\ShipmentInterface $shipment);

    /**
     * Creates a shipment regarding to the sale type.
     *
     * @param Invoice\InvoiceInterface $invoice
     *
     * @return Invoice\InvoiceLineInterface
     */
    public function createLineForInvoice(Invoice\InvoiceInterface $invoice);

    /**
     * Creates an address regarding to the sale type.
     *
     * @param Model\SaleInterface $sale
     *
     * @return Payment\PaymentInterface
     */
    public function createPaymentForSale(Model\SaleInterface $sale);

    /**
     * Creates a shipment regarding to the sale type.
     *
     * @param Model\SaleInterface $sale
     *
     * @return Shipment\ShipmentInterface
     */
    public function createShipmentForSale(Model\SaleInterface $sale);

    /**
     * Creates a invoice regarding to the sale type.
     *
     * @param Model\SaleInterface $sale
     *
     * @return Invoice\InvoiceInterface
     */
    public function createInvoiceForSale(Model\SaleInterface $sale);
}
