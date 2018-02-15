<?php

namespace Ekyna\Component\Commerce\Shipment\Calculator;

use Ekyna\Component\Commerce\Common\Model as Common;
use Ekyna\Component\Commerce\Invoice\Calculator\InvoiceCalculatorInterface;
use Ekyna\Component\Commerce\Shipment\Model as Shipment;
use Ekyna\Component\Commerce\Stock\Model as Stock;
use Ekyna\Component\Commerce\Subject\SubjectHelperInterface;

/**
 * Class ShipmentCalculator
 * @package Ekyna\Component\Commerce\Shipment\Calculator
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ShipmentCalculator implements ShipmentCalculatorInterface
{
    /**
     * @var SubjectHelperInterface
     */
    private $subjectHelper;

    /**
     * @var InvoiceCalculatorInterface
     */
    private $invoiceCalculator;


    /**
     * Constructor.
     *
     * @param SubjectHelperInterface $subjectHelper
     */
    public function __construct(SubjectHelperInterface $subjectHelper)
    {
        $this->subjectHelper = $subjectHelper;
    }

    /**
     * Sets the invoice calculator.
     *
     * @param InvoiceCalculatorInterface $calculator
     */
    public function setInvoiceCalculator(InvoiceCalculatorInterface $calculator)
    {
        $this->invoiceCalculator = $calculator;
    }

    /**
     * @inheritdoc
     */
    public function isShipped(Common\SaleItemInterface $saleItem)
    {
        // If compound with only public children
        if ($saleItem->isCompound() && !$saleItem->hasPrivateChildren()) {
            // Shipped if any of it's children is
            foreach ($saleItem->getChildren() as $child) {
                if ($this->isShipped($child)) {
                    return true;
                }
            }

            return false;
        }

        $sale = $saleItem->getSale();
        if (!$sale instanceof Shipment\ShipmentSubjectInterface) {
            return false;
        }

        foreach ($sale->getShipments() as $shipment) {
            foreach ($shipment->getItems() as $line) {
                if ($line->getSaleItem() === $saleItem) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @inheritdoc
     */
    public function calculateAvailableQuantity(Common\SaleItemInterface $saleItem, Shipment\ShipmentInterface $ignore = null)
    {
        /** @var Common\SaleItemInterface $saleItem */
        if (!$this->hasStockableSubject($saleItem)) {
            return INF;
        }

        // TODO Packaging format
        /** @var Stock\StockAssignmentsInterface $saleItem */
        $quantity = 0;
        foreach ($saleItem->getStockAssignments() as $assignment) {
            $quantity += $assignment->getShippableQuantity();
        }

        if (
            null !== $ignore && null !== $ignore->getId() && !$ignore->isReturn() &&
            Shipment\ShipmentStates::isStockableState($ignore->getState())
        ) {
            foreach ($ignore->getItems() as $item) {
                if ($item->getSaleItem() === $saleItem) {
                    $quantity += $item->getQuantity();

                    break;
                }
            }
        }

        return max($quantity, 0);
    }

    /**
     * @inheritdoc
     *
     * @todo Add bool $strict parameter : really shipped and not created/prepared
     */
    public function calculateShippableQuantity(Common\SaleItemInterface $saleItem, Shipment\ShipmentInterface $ignore = null)
    {
        // TODO Return zero if not shippable (?)

        // Quantity = Sold - Shipped - Returned (ignoring current)

        // TODO Packaging format
        $quantity = $saleItem->getTotalQuantity();
        $quantity -= $this->invoiceCalculator->calculateCreditedQuantity($saleItem);
        $quantity -= $this->calculateShippedQuantity($saleItem, $ignore);
        $quantity += $this->calculateReturnedQuantity($saleItem);

        return max($quantity, 0);
    }

    /**
     * @inheritdoc
     */
    public function calculateReturnableQuantity(Common\SaleItemInterface $saleItem, Shipment\ShipmentInterface $ignore = null)
    {
        // Quantity = Shipped - Returned (ignoring current)

        // TODO Packaging format
        $quantity = $this->calculateShippedQuantity($saleItem)
            - $this->calculateReturnedQuantity($saleItem, $ignore);

        return max($quantity, 0);
    }

    /**
     * @inheritdoc
     */
    public function calculateShippedQuantity(Common\SaleItemInterface $saleItem, Shipment\ShipmentInterface $ignore = null)
    {
        $sale = $saleItem->getSale();

        if (!$sale instanceof Shipment\ShipmentSubjectInterface) {
            return 0;
        }

        // TODO Packaging format
        $quantity = 0;

        foreach ($sale->getShipments() as $shipment) {
            if (null !== $ignore && $shipment === $ignore) {
                continue;
            }

            if ($shipment->isReturn() || $shipment->getState() !== Shipment\ShipmentStates::STATE_SHIPPED) {
                continue;
            }

            foreach ($shipment->getItems() as $line) {
                if ($line->getSaleItem() === $saleItem) {
                    $quantity += $line->getQuantity();
                }
            }
        }

        return $quantity;
    }

    /**
     * @inheritdoc
     */
    public function calculateReturnedQuantity(Common\SaleItemInterface $saleItem, Shipment\ShipmentInterface $ignore = null)
    {
        $sale = $saleItem->getSale();

        if (!$sale instanceof Shipment\ShipmentSubjectInterface) {
            return 0;
        }

        // TODO Packaging format
        $quantity = 0;

        foreach ($sale->getShipments() as $shipment) {
            if (null !== $ignore && $shipment === $ignore) {
                continue;
            }

            if (!$shipment->isReturn() || $shipment->getState() !== Shipment\ShipmentStates::STATE_RETURNED) {
                continue;
            }

            foreach ($shipment->getItems() as $shipmentItem) {
                if ($shipmentItem->getSaleItem() === $saleItem) {
                    $quantity += $shipmentItem->getQuantity();
                }
            }
        }

        return $quantity;
    }

    /**
     * @inheritdoc
     */
    public function buildShipmentQuantityMap(Shipment\ShipmentSubjectInterface $subject)
    {
        $quantities = [];

        if ($subject instanceof Common\SaleInterface) {
            foreach ($subject->getItems() as $item) {
                $this->buildSaleItemQuantities($item, $quantities);
            }
        }

        return $quantities;
    }

    /**
     * Builds the sale item quantities recursively.
     *
     * @param Common\SaleItemInterface $item
     * @param array                    $quantities
     */
    private function buildSaleItemQuantities(Common\SaleItemInterface $item, array &$quantities)
    {
        // Skip compound with only public children
        if (!($item->isCompound() && !$item->hasPrivateChildren())) {
            $sold = $item->getTotalQuantity() - $this->invoiceCalculator->calculateCreditedQuantity($item);

            $quantities[$item->getId()] = [
                'sold'     => $sold,
                'shipped'  => $this->calculateShippedQuantity($item),
                'returned' => $this->calculateReturnedQuantity($item),
            ];
        }

        if ($item->hasChildren()) {
            foreach ($item->getChildren() as $child) {
                $this->buildSaleItemQuantities($child, $quantities);
            }
        }
    }

    /**
     * Returns whether or not the sale item has a stockable subject.
     *
     * @param Common\SaleItemInterface $saleItem
     *
     * @return bool
     */
    private function hasStockableSubject(
        Common\SaleItemInterface $saleItem
    ) {
        if (!$saleItem instanceof Stock\StockAssignmentsInterface) {
            return false;
        }

        if (null === $subject = $this->subjectHelper->resolve($saleItem)) {
            return false;
        }

        if (!$subject instanceof Stock\StockSubjectInterface) {
            return false;
        }

        if ($subject->isStockCompound()) {
            return false;
        }

        if ($subject->getStockMode() === Stock\StockSubjectModes::MODE_DISABLED) {
            return false;
        }

        return true;
    }
}
