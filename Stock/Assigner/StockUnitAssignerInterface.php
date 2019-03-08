<?php

namespace Ekyna\Component\Commerce\Stock\Assigner;

use Ekyna\Component\Commerce\Common\Model\SaleItemInterface;
use Ekyna\Component\Commerce\Invoice\Model\InvoiceLineInterface;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentItemInterface;

/**
 * Interface StockUnitAssignerInterface
 * @package Ekyna\Component\Commerce\Stock\Assigner
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface StockUnitAssignerInterface
{
    /**
     * Assigns the sale item to stock units by creating stock assignments.
     *
     * @param SaleItemInterface $item
     */
    public function assignSaleItem(SaleItemInterface $item);

    /**
     * Applies the sale item quantity change to stock units.
     *
     * @param SaleItemInterface $item
     */
    public function applySaleItem(SaleItemInterface $item);

    /**
     * Detaches the sale item from stock units by removing stock assignments.
     *
     * @param SaleItemInterface $item
     */
    public function detachSaleItem(SaleItemInterface $item);

    /**
     * Assigns the shipment item to stock units
     * by updating the stock assignments's shipped quantities.
     *
     * @param ShipmentItemInterface $item
     */
    public function assignShipmentItem(ShipmentItemInterface $item);

    /**
     * Applies the shipment item quantity change to stock units
     * by updating the stock assignment's shipped quantities.
     *
     * @param ShipmentItemInterface $item
     */
    public function applyShipmentItem(ShipmentItemInterface $item);

    /**
     * Detaches the shipment item from stock units
     * by updating the stock assignment's shipped quantities.
     *
     * @param ShipmentItemInterface $item
     */
    public function detachShipmentItem(ShipmentItemInterface $item);

    /**
     * Assigns the invoice line to stock units
     * by updating the stock assignments's sold quantities.
     *
     * @param InvoiceLineInterface $line
     */
    public function assignInvoiceLine(InvoiceLineInterface $line);

    /**
     * Applies the invoice line quantity change to stock units
     * by updating the stock assignment's sold quantities.
     *
     * @param InvoiceLineInterface $line
     */
    public function applyInvoiceLine(InvoiceLineInterface $line);

    /**
     * Detaches the invoice line from stock units
     * by updating the stock assignment's sold quantities.
     *
     * @param InvoiceLineInterface $line
     */
    public function detachInvoiceLine(InvoiceLineInterface $line);

    /**
     * Returns whether or not the given item supports assignments.
     *
     * @param SaleItemInterface $item
     *
     * @return bool
     */
    public function supportsAssignment(SaleItemInterface $item);
}
