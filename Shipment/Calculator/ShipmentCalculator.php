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
    public function calculateAvailableQuantity(
        Common\SaleItemInterface $saleItem,
        Shipment\ShipmentInterface $ignore = null
    ) {
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
    public function calculateShippableQuantity(
        Common\SaleItemInterface $saleItem,
        Shipment\ShipmentInterface $ignore = null
    ) {
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
    public function calculateReturnableQuantity(
        Common\SaleItemInterface $saleItem,
        Shipment\ShipmentInterface $ignore = null
    ) {
        // Quantity = Shipped - Returned (ignoring current)

        // TODO Packaging format
        $quantity = $this->calculateShippedQuantity($saleItem)
            - $this->calculateReturnedQuantity($saleItem, $ignore);

        return max($quantity, 0);
    }

    /**
     * @inheritdoc
     */
    public function calculateShippedQuantity(
        Common\SaleItemInterface $saleItem,
        Shipment\ShipmentInterface $ignore = null
    ) {
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
    public function calculateReturnedQuantity(
        Common\SaleItemInterface $saleItem,
        Shipment\ShipmentInterface $ignore = null
    ) {
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
     * @inheritdoc
     */
    public function buildRemainingList(Shipment\ShipmentInterface $shipment)
    {
        $sale = $shipment->getSale();

        $id = $shipment->getId();
        $date = $shipment->getShippedAt() ?? $shipment->getCreatedAt();

        $shipments = $sale
            ->getShipments()
            ->filter(function (Shipment\ShipmentInterface $s) use ($id, $date) {
                if ($s->getId() == $id) {
                    return false;
                }

                if (!Shipment\ShipmentStates::isStockableState($s->getState())) {
                    return false;
                }

                if ($s->getShippedAt() > $date) {
                    return false;
                }

                return true;
            })
            ->toArray();

        $shipments[] = $shipment;
        $list = new Shipment\RemainingList();

        foreach ($sale->getItems() as $item) {
            $this->buildSaleItemRemaining($item, $list, $shipments);
        }

        $esd = null;
        foreach ($list->getEntries() as $entry) {
            /** @var Common\SaleItemInterface $saleItem */
            $saleItem = $entry->getSaleItem();

            if (!$saleItem instanceof Stock\StockAssignmentsInterface) {
                continue;
            }

            /** @var Stock\StockSubjectInterface */
            if (null === $subject = $this->subjectHelper->resolve($saleItem, false)) {
                continue;
            }

            if (!$subject instanceof Stock\StockSubjectInterface) {
                continue;
            }

            $quantity = $entry->getQuantity();
            $eda = null;
            foreach ($saleItem->getStockAssignments() as $assignment) {
                $quantity -= $assignment->getShippableQuantity();

                // Abort if item is shippable
                if (0 >= $quantity) {
                    break;
                }

                // Next assignment if we don't have an EDA
                if (null === $date = $assignment->getStockUnit()->getEstimatedDateOfArrival()) {
                    continue;
                }

                if (is_null($eda) || $eda < $date) {
                    $eda = $date;
                }
            }

            if (is_null($eda)) {
                continue;
            }

            if (is_null($esd) || $esd < $eda) {
                $esd = $eda;
            }
        }

        if ($esd > new \DateTime()) {
            $list->setEstimatedShippingDate($esd);
        }

        return $list;
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
            $quantities[$item->getId()] = [
                'total'    => $item->getTotalQuantity(),
                'credited' => $this->invoiceCalculator->calculateCreditedQuantity($item),
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
     * Calculate the sale item remaining quantity.
     *
     * @param Common\SaleItemInterface     $saleItem
     * @param Shipment\RemainingList       $list
     * @param Shipment\ShipmentInterface[] $shipments
     */
    private function buildSaleItemRemaining(
        Common\SaleItemInterface $saleItem,
        Shipment\RemainingList $list,
        array $shipments
    ) {

        // Not for compound item with only public children
        if (!($saleItem->isCompound() && !$saleItem->hasPrivateChildren())) {
            $quantity = $saleItem->getTotalQuantity();

            foreach ($shipments as $shipment) {
                foreach ($shipment->getItems() as $item) {
                    if ($item->getSaleItem() === $saleItem) {
                        $quantity += $shipment->isReturn() ? $item->getQuantity() : -$item->getQuantity();

                        continue 2;
                    }
                }
            }

            if (0 < $quantity) {
                $entry = new Shipment\RemainingEntry();
                $entry
                    ->setSaleItem($saleItem)
                    ->setQuantity($quantity);

                $list->addEntry($entry);
            }
        }

        foreach ($saleItem->getChildren() as $child) {
            $this->buildSaleItemRemaining($child, $list, $shipments);
        }
    }

    /**
     * Returns whether or not the sale item has a stockable subject.
     *
     * @param Common\SaleItemInterface $saleItem
     *
     * @return bool
     */
    private function hasStockableSubject(Common\SaleItemInterface $saleItem)
    {
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
