<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Stock\Assigner;

use Ekyna\Component\Commerce\Manufacture\Model\ProductionInterface;
use Ekyna\Component\Commerce\Manufacture\Model\ProductionItemInterface;
use Ekyna\Component\Commerce\Order\Model\OrderInvoiceLineInterface;
use Ekyna\Component\Commerce\Order\Model\OrderItemInterface;
use Ekyna\Component\Commerce\Order\Model\OrderShipmentItemInterface;
use Ekyna\Component\Commerce\Stock\Model\AssignableInterface;

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
     * @param OrderItemInterface $item
     */
    public function assignOrderItem(OrderItemInterface $item): void;

    /**
     * Applies the sale item quantity change to stock units.
     *
     * @param OrderItemInterface $item
     */
    public function applyOrderItem(OrderItemInterface $item): void;

    /**
     * Detaches the sale item from stock units by removing stock assignments.
     *
     * @param OrderItemInterface $item
     */
    public function detachOrderItem(OrderItemInterface $item): void;

    public function assignProductionItem(ProductionItemInterface $item): void;

    public function applyProductionItem(ProductionItemInterface $item): void;

    public function detachProductionItem(ProductionItemInterface $item): void;

    public function assignProduction(ProductionInterface $production): void;

    public function applyProduction(ProductionInterface $production): void;

    public function detachProduction(ProductionInterface $production): void;

    /**
     * Assigns the shipment item to stock units
     * by updating the stock assignment's shipped quantities.
     *
     * @param OrderShipmentItemInterface $item
     */
    public function assignShipmentItem(OrderShipmentItemInterface $item): void;

    /**
     * Applies the shipment item quantity change to stock units
     * by updating the stock assignment's shipped quantities.
     *
     * @param OrderShipmentItemInterface $item
     */
    public function applyShipmentItem(OrderShipmentItemInterface $item): void;

    /**
     * Detaches the shipment item from stock units
     * by updating the stock assignment's shipped quantities.
     *
     * @param OrderShipmentItemInterface $item
     */
    public function detachShipmentItem(OrderShipmentItemInterface $item): void;

    /**
     * Assigns the invoice line to stock units
     * by updating the stock assignment's sold quantities.
     *
     * @param OrderInvoiceLineInterface $line
     */
    public function assignInvoiceLine(OrderInvoiceLineInterface $line): void;

    /**
     * Applies the invoice line quantity change to stock units
     * by updating the stock assignment's sold quantities.
     *
     * @param OrderInvoiceLineInterface $line
     */
    public function applyInvoiceLine(OrderInvoiceLineInterface $line): void;

    /**
     * Detaches the invoice line from stock units
     * by updating the stock assignment's sold quantities.
     *
     * @param OrderInvoiceLineInterface $line
     */
    public function detachInvoiceLine(OrderInvoiceLineInterface $line): void;

    /**
     * Returns whether the given assignable supports assignments.
     *
     * @param AssignableInterface $assignable
     *
     * @return bool
     */
    public function supportsAssignment(AssignableInterface $assignable): bool;
}
