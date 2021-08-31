<?php

namespace Ekyna\Component\Commerce\Shipment\Calculator;

use Ekyna\Component\Commerce\Common\Model\SaleInterface as Sale;
use Ekyna\Component\Commerce\Common\Model\SaleItemInterface as SaleItem;
use Ekyna\Component\Commerce\Invoice\Calculator\InvoiceSubjectCalculatorInterface;
use Ekyna\Component\Commerce\Shipment\Model\RemainingEntry;
use Ekyna\Component\Commerce\Shipment\Model\RemainingList;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentInterface as Shipment;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentStates;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentSubjectInterface as Subject;
use Ekyna\Component\Commerce\Stock\Model as Stock;
use Ekyna\Component\Commerce\Subject\SubjectHelperInterface;

/**
 * Class ShipmentSubjectCalculator
 * @package Ekyna\Component\Commerce\Shipment\Calculator
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ShipmentSubjectCalculator implements ShipmentSubjectCalculatorInterface
{
    /**
     * @var SubjectHelperInterface
     */
    protected $subjectHelper;

    /**
     * @var InvoiceSubjectCalculatorInterface
     */
    protected $invoiceCalculator;


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
     * @param InvoiceSubjectCalculatorInterface $calculator
     */
    public function setInvoiceCalculator(InvoiceSubjectCalculatorInterface $calculator): void
    {
        $this->invoiceCalculator = $calculator;
    }

    /**
     * @inheritdoc
     */
    public function isShipped(SaleItem $saleItem): bool
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
        if (!$sale instanceof Subject) {
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
    public function calculateAvailableQuantity(SaleItem $saleItem, Shipment $ignore = null): float
    {
        if ($saleItem->isCompound()) {
            $quantity = INF;
            foreach ($saleItem->getChildren() as $child) {
                $cQty = $this->calculateAvailableQuantity($child, $ignore) / $child->getQuantity();
                $quantity = min($quantity, $cQty);
            }

            return $quantity;
        }

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
            null !== $ignore
            && null !== $ignore->getId()
            && !$ignore->isReturn()
            && ShipmentStates::isStockableState($ignore, true)
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
    public function calculateShippableQuantity(SaleItem $saleItem, Shipment $ignore = null): float
    {
        // TODO Return zero if not shippable (?)

        // Quantity = Sold - Shipped (ignoring current) + Returned

        // TODO Packaging format
        //$quantity = max($saleItem->getTotalQuantity(), $this->invoiceCalculator->calculateInvoicedQuantity($saleItem));
        $quantity = $this->invoiceCalculator->calculateSoldQuantity($saleItem);
        $quantity -= $this->calculateShippedQuantity($saleItem, $ignore);
        $quantity += $this->calculateReturnedQuantity($saleItem);

        return max($quantity, 0);
    }

    /**
     * @inheritdoc
     */
    public function calculateReturnableQuantity(SaleItem $saleItem, Shipment $ignore = null): float
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
    public function calculateShippedQuantity(SaleItem $saleItem, Shipment $ignore = null): float
    {
        return $this->calculateQuantity($saleItem, false, $ignore);
    }

    /**
     * @inheritdoc
     */
    public function calculateReturnedQuantity(SaleItem $saleItem, Shipment $ignore = null): float
    {
        return $this->calculateQuantity($saleItem, true, $ignore);
    }

    /**
     * @inheritdoc
     */
    public function buildShipmentQuantityMap(Subject $subject): array
    {
        $quantities = [];

        if ($subject instanceof Sale) {
            foreach ($subject->getItems() as $item) {
                $this->buildSaleItemQuantities($item, $quantities);
            }
        }

        return $quantities;
    }

    /**
     * @inheritdoc
     */
    public function buildRemainingList(Shipment $shipment): RemainingList
    {
        $sale = $shipment->getSale();

        $id = $shipment->getId();
        $date = $shipment->getShippedAt() ?? $shipment->getCreatedAt();

        $shipments = $sale
            ->getShipments()
            ->filter(function (Shipment $s) use ($id, $date) {
                if ($s->getId() == $id) {
                    return false;
                }

                if (!ShipmentStates::isStockableState($s, false)) {
                    return false;
                }

                if ($s->getShippedAt() > $date) {
                    return false;
                }

                return true;
            })
            ->toArray();

        $shipments[] = $shipment;
        $list = new RemainingList();

        foreach ($sale->getItems() as $item) {
            $this->buildSaleItemRemaining($item, $list, $shipments);
        }

        $esd = null;
        foreach ($list->getEntries() as $entry) {
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
     * Calculates the shipped or returned quantity.
     *
     * @param SaleItem      $saleItem
     * @param bool          $return
     * @param Shipment|null $ignore
     *
     * @return float
     */
    private function calculateQuantity(SaleItem $saleItem, bool $return = false, Shipment $ignore = null): float
    {
        $sale = $saleItem->getSale();

        if (!$sale instanceof Subject) {
            return 0;
        }

        if ($saleItem->isCompound()) {
            $quantity = INF;
            foreach ($saleItem->getChildren() as $child) {
                $cQty = $this->calculateQuantity($child, $return, $ignore) / $child->getQuantity();
                $quantity = min($quantity, $cQty);
            }

            return $quantity;
        }

        // TODO Packaging format
        $quantity = 0;

        foreach ($sale->getShipments(!$return) as $shipment) {
            if ($ignore === $shipment) {
                continue;
            }

            if (!ShipmentStates::isStockableState($shipment, false)) {
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
     * Builds the sale item quantities recursively.
     *
     * @param SaleItem $item
     * @param array    $quantities
     */
    private function buildSaleItemQuantities(SaleItem $item, array &$quantities): void
    {
        // Skip compound with only public children
        if (!($item->isCompound() && !$item->hasPrivateChildren())) {
            $quantities[$item->getId()] = [
                'sold'     => $this->invoiceCalculator->calculateSoldQuantity($item),
                'invoiced' => $this->invoiceCalculator->isInvoiced($item),
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
     * @param SaleItem      $saleItem
     * @param RemainingList $list
     * @param Shipment[]    $shipments
     */
    private function buildSaleItemRemaining(SaleItem $saleItem, RemainingList $list, array $shipments): void
    {
        // Not for compound item with only public children
        if (!($saleItem->isCompound() && !$saleItem->hasPrivateChildren())) {
            $quantity = max(
                $saleItem->getTotalQuantity(),
                $this->invoiceCalculator->calculateInvoicedQuantity($saleItem)
            );

            foreach ($shipments as $shipment) {
                foreach ($shipment->getItems() as $item) {
                    if ($item->getSaleItem() === $saleItem) {
                        $quantity += $shipment->isReturn() ? $item->getQuantity() : -$item->getQuantity();

                        continue 2;
                    }
                }
            }

            if (0 < $quantity) {
                $entry = new RemainingEntry();
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
     * @param SaleItem $saleItem
     *
     * @return bool
     */
    private function hasStockableSubject(SaleItem $saleItem): bool
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

        /*if ($subject->isStockCompound()) {
            return false;
        }*/

        if ($subject->getStockMode() === Stock\StockSubjectModes::MODE_DISABLED) {
            return false;
        }

        return true;
    }
}
