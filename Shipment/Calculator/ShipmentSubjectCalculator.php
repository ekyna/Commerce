<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Shipment\Calculator;

use DateTime;
use Decimal\Decimal;
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

use function is_null;
use function max;
use function min;

use const INF;

/**
 * Class ShipmentSubjectCalculator
 * @package Ekyna\Component\Commerce\Shipment\Calculator
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ShipmentSubjectCalculator implements ShipmentSubjectCalculatorInterface
{
    protected SubjectHelperInterface            $subjectHelper;
    protected InvoiceSubjectCalculatorInterface $invoiceCalculator;

    public function __construct(SubjectHelperInterface $subjectHelper)
    {
        $this->subjectHelper = $subjectHelper;
    }

    public function setInvoiceCalculator(InvoiceSubjectCalculatorInterface $calculator): void
    {
        $this->invoiceCalculator = $calculator;
    }

    public function isShipped(SaleItem $saleItem): bool
    {
        // If compound with only public children
        if ($saleItem->isCompound() && !$saleItem->hasPrivateChildren()) {
            // Shipped if any of its children is
            foreach ($saleItem->getChildren() as $child) {
                if ($this->isShipped($child)) {
                    return true;
                }
            }

            return false;
        }

        $sale = $saleItem->getRootSale();
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

    public function calculateAvailableQuantity(SaleItem $saleItem, Shipment $ignore = null): Decimal
    {
        if ($saleItem->isCompound()) {
            $quantity = new Decimal(INF);
            foreach ($saleItem->getChildren() as $child) {
                $cQty = $this
                    ->calculateAvailableQuantity($child, $ignore)
                    ->div($child->getQuantity());

                $quantity = min($quantity, $cQty);
            }

            return $quantity;
        }

        if (!$this->hasStockableSubject($saleItem)) {
            return new Decimal(INF);
        }

        // TODO Packaging format
        /** @var Stock\AssignableInterface $saleItem */
        $quantity = new Decimal(0);
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

        return max($quantity, new Decimal(0));
    }

    /**
     * @TODO Add bool $strict parameter : really shipped and not created/prepared
     */
    public function calculateShippableQuantity(SaleItem $saleItem, Shipment $ignore = null): Decimal
    {
        // TODO Return zero if not shippable (physical) ?

        // Quantity = Sold - Shipped (ignoring current) + Returned

        // TODO Packaging format
        $quantity = $this->invoiceCalculator->calculateSoldQuantity($saleItem);
        $quantity -= $this->calculateShippedQuantity($saleItem, $ignore);
        $quantity += $this->calculateReturnedQuantity($saleItem);

        return max($quantity, new Decimal(0));
    }

    public function calculateReturnableQuantity(SaleItem $saleItem, Shipment $ignore = null): Decimal
    {
        // Quantity = Shipped - Returned (ignoring current)

        // TODO Packaging format
        $quantity = $this->calculateShippedQuantity($saleItem)
            - $this->calculateReturnedQuantity($saleItem, $ignore);

        return max($quantity, new Decimal(0));
    }

    public function calculateShippedQuantity(SaleItem $saleItem, Shipment $ignore = null): Decimal
    {
        return $this->calculateQuantity($saleItem, false, $ignore);
    }

    public function calculateReturnedQuantity(SaleItem $saleItem, Shipment $ignore = null): Decimal
    {
        return $this->calculateQuantity($saleItem, true, $ignore);
    }

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

    public function buildRemainingList(Shipment $shipment): RemainingList
    {
        $sale = $shipment->getSale();

        $id = $shipment->getId();
        $date = $shipment->getShippedAt() ?? $shipment->getCreatedAt();

        $shipments = $sale
            ->getShipments()
            ->filter(function (Shipment $s) use ($id, $date) {
                if ($s->getId() === $id) {
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

            if (!$saleItem instanceof Stock\AssignableInterface) {
                continue;
            }

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

        if ($esd > new DateTime()) {
            $list->setEstimatedShippingDate($esd);
        }

        return $list;
    }

    /**
     * Calculates the shipped or returned quantity.
     */
    private function calculateQuantity(SaleItem $saleItem, bool $return = false, Shipment $ignore = null): Decimal
    {
        $sale = $saleItem->getRootSale();

        if (!$sale instanceof Subject) {
            return new Decimal(0);
        }

        if ($saleItem->isCompound()) {
            $quantity = new Decimal(INF);
            foreach ($saleItem->getChildren() as $child) {
                $cQty = $this
                    ->calculateQuantity($child, $return, $ignore)
                    ->div($child->getQuantity());

                $quantity = min($quantity, $cQty);
            }

            return $quantity;
        }

        // TODO Packaging format
        $quantity = new Decimal(0);

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
     * @param array<Shipment> $shipments
     */
    private function buildSaleItemRemaining(SaleItem $saleItem, RemainingList $list, array $shipments): void
    {
        // Not for compound item with only public children
        if (!($saleItem->isCompound() && !$saleItem->hasPrivateChildren())) {
            $quantity = $this->invoiceCalculator->calculateSoldQuantity($saleItem);

            foreach ($shipments as $shipment) {
                foreach ($shipment->getItems() as $item) {
                    if ($item->getSaleItem() === $saleItem) {
                        $quantity += $shipment->isReturn() ? $item->getQuantity() : -$item->getQuantity();

                        continue 2;
                    }
                }
            }

            if (0 < $quantity) {
                $list->addEntry(new RemainingEntry($saleItem, $quantity));
            }
        }

        foreach ($saleItem->getChildren() as $child) {
            $this->buildSaleItemRemaining($child, $list, $shipments);
        }
    }

    /**
     * Returns whether the sale item has a stockable subject.
     */
    private function hasStockableSubject(SaleItem $saleItem): bool
    {
        if (!$saleItem instanceof Stock\AssignableInterface) {
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
