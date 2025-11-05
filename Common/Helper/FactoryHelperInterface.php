<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Common\Helper;

use Ekyna\Component\Commerce\Common\Model;
use Ekyna\Component\Commerce\Invoice\Model as Invoice;
use Ekyna\Component\Commerce\Payment\Model as Payment;
use Ekyna\Component\Commerce\Shipment\Model as Shipment;

/**
 * Interface FactoryHelperInterface
 * @package Ekyna\Component\Commerce\Common
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface FactoryHelperInterface
{
    /**
     * Creates an address regarding the sale type.
     */
    public function createAddressForSale(
        Model\SaleInterface     $sale,
        ?Model\AddressInterface $source
    ): Model\SaleAddressInterface;

    /**
     * Creates an adjustment for the given adjustable.
     */
    public function createAdjustmentFor(Model\AdjustableInterface $adjustable): Model\AdjustmentInterface;

    /**
     * Creates an attachment regarding the sale type.
     */
    public function createAttachmentForSale(Model\SaleInterface $sale): Model\SaleAttachmentInterface;

    /**
     * Creates a notification regarding the sale type.
     */
    public function createNotificationForSale(Model\SaleInterface $sale): Model\SaleNotificationInterface;

    /**
     * Creates an adjustment regarding the sale item type.
     */
    public function createAdjustmentForItem(Model\SaleItemInterface $item): Model\AdjustmentInterface;

    /**
     * Creates an adjustment regarding the sale type.
     */
    public function createAdjustmentForSale(Model\SaleInterface $sale): Model\AdjustmentInterface;

    /**
     * Creates a sale item regarding the sale type.
     */
    public function createItemForSale(Model\SaleInterface $sale): Model\SaleItemInterface;

    /**
     * Creates a shipment regarding the sale type.
     */
    public function createItemForShipment(Shipment\ShipmentInterface $shipment): Shipment\ShipmentItemInterface;

    /**
     * Creates a shipment regarding the sale type.
     */
    public function createLineForInvoice(Invoice\InvoiceInterface $invoice): Invoice\InvoiceLineInterface;

    /**
     * Creates an address regarding the sale type.
     */
    public function createPaymentForSale(Model\SaleInterface $sale): Payment\PaymentInterface;

    /**
     * Creates a shipment regarding the sale type.
     */
    public function createShipmentForSale(Model\SaleInterface $sale): Shipment\ShipmentInterface;

    /**
     * Creates an invoice regarding the sale type.
     */
    public function createInvoiceForSale(Model\SaleInterface $sale): Invoice\InvoiceInterface;
}
