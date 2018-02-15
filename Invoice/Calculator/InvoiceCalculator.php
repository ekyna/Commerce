<?php

namespace Ekyna\Component\Commerce\Invoice\Calculator;

use Ekyna\Component\Commerce\Common\Model\SaleAdjustmentInterface;
use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Common\Model\SaleItemInterface;
use Ekyna\Component\Commerce\Document\Model\DocumentLineTypes;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Invoice\Model as Invoice;
use Ekyna\Component\Commerce\Shipment\Calculator\ShipmentCalculatorInterface;

/**
 * Class QuantityCalculator
 * @package Ekyna\Component\Commerce\Invoice\Calculator
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class InvoiceCalculator implements InvoiceCalculatorInterface
{
    /**
     * @var ShipmentCalculatorInterface
     */
    private $shipmentCalculator;


    /**
     * Sets the shipment calculator.
     *
     * @param ShipmentCalculatorInterface $calculator
     */
    public function setShipmentCalculator($calculator)
    {
        $this->shipmentCalculator = $calculator;
    }

    /**
     * @inheritdoc
     */
    public function isInvoiced($itemOrAdjustment)
    {
        if ($itemOrAdjustment instanceof SaleItemInterface) {
            // If compound with only public children
            if ($itemOrAdjustment->isCompound() && !$itemOrAdjustment->hasPrivateChildren()) {
                // Invoiced if any of it's children is
                foreach ($itemOrAdjustment->getChildren() as $child) {
                    if ($this->isInvoiced($child)) {
                        return true;
                    }
                }

                return false;
            }

            $sale = $itemOrAdjustment->getSale();
            if (!$sale instanceof Invoice\InvoiceSubjectInterface) {
                return false;
            }

            foreach ($sale->getInvoices() as $invoice) {
                foreach ($invoice->getLinesByType(DocumentLineTypes::TYPE_GOOD) as $line) {
                    if ($line->getSaleItem() === $itemOrAdjustment) {
                        return true;
                    }
                }
            }

            return false;
        }

        if ($itemOrAdjustment instanceof SaleAdjustmentInterface) {
            $sale = $itemOrAdjustment->getSale();
            if (!$sale instanceof Invoice\InvoiceSubjectInterface) {
                return false;
            }

            foreach ($sale->getInvoices() as $invoice) {
                foreach ($invoice->getLinesByType(DocumentLineTypes::TYPE_DISCOUNT) as $line) {
                    if ($line->getSaleAdjustment() === $itemOrAdjustment) {
                        return true;
                    }
                }
            }

            return false;
        }

        throw new InvalidArgumentException(
            "Expected instance of " . SaleItemInterface::class . " or " . SaleAdjustmentInterface::class
        );
    }

    /**
     * @inheritdoc
     */
    public function calculateInvoiceableQuantity($subject, Invoice\InvoiceInterface $ignore = null)
    {
        // Good line case
        if ($subject instanceof SaleItemInterface) {
            $sale = $subject->getSale();
            if (!$sale instanceof Invoice\InvoiceSubjectInterface) {
                return 0;
            }

            // Quantity = Total - Invoiced (ignoring current invoice)
            $quantity = $subject->getTotalQuantity()
                - $this->calculateInvoicedQuantity($subject, $ignore);

            return max(0, $quantity);
        }

        // Discount line case
        if ($subject instanceof SaleAdjustmentInterface) {
            $sale = $subject->getSale();
            if (!$sale instanceof Invoice\InvoiceSubjectInterface) {
                return 0;
            }

            // Discounts must be dispatched into all invoices
            return 1;
        }

        // Shipment line case
        if ($subject instanceof SaleInterface) {
            if (!$subject instanceof Invoice\InvoiceSubjectInterface) {
                return 0;
            }

            $invoiced = $this->calculateInvoicedQuantity($subject, $ignore);

            // Shipment must be invoiced once
            return min(1, max(0, 1 - $invoiced));
        }

        throw new InvalidArgumentException(sprintf(
            "Unexpected instance of %s, %s or %s",
            SaleInterface::class,
            SaleItemInterface::class,
            SaleAdjustmentInterface::class
        ));
    }

    /**
     * @inheritdoc
     */
    public function calculateCreditableQuantity($subject, Invoice\InvoiceInterface $ignore = null)
    {
        // Good line case
        if ($subject instanceof SaleItemInterface) {
            $sale = $subject->getSale();
            if (!$sale instanceof Invoice\InvoiceSubjectInterface) {
                return 0;
            }

            // Quantity = Invoiced - Shipped + Returned - Credited (ignoring current credit)
            $quantity = $this->calculateInvoicedQuantity($subject)
                - $this->shipmentCalculator->calculateShippedQuantity($subject)
                + $this->shipmentCalculator->calculateReturnedQuantity($subject)
                - $this->calculateCreditedQuantity($subject, $ignore);

            return max(0, $quantity);
        }

        // Discount line case
        if ($subject instanceof SaleAdjustmentInterface) {
            $sale = $subject->getSale();
            if (!$sale instanceof Invoice\InvoiceSubjectInterface) {
                return 0;
            }

            // Discounts must be dispatched into all invoices
            return 1;
        }

        // Shipment line case
        if ($subject instanceof SaleInterface) {
            if (!$subject instanceof Invoice\InvoiceSubjectInterface) {
                return 0;
            }

            // Shipment can be credited once
            return max(1, $this->calculateInvoicedQuantity($subject));
        }

        throw new InvalidArgumentException(sprintf(
            "Unexpected instance of %s, %s or %s",
            SaleInterface::class,
            SaleItemInterface::class,
            SaleAdjustmentInterface::class
        ));
    }

    /**
     * @inheritdoc
     */
    public function calculateInvoicedQuantity($subject, Invoice\InvoiceInterface $ignore = null)
    {
        // Good line case
        if ($subject instanceof SaleItemInterface) {
            $sale = $subject->getSale();
            if (!$sale instanceof Invoice\InvoiceSubjectInterface) {
                return 0;
            }

            $quantity = 0;
            foreach ($sale->getInvoices() as $invoice) {
                if (Invoice\InvoiceTypes::isCredit($invoice) || $invoice === $ignore) {
                    continue;
                }

                foreach ($invoice->getLinesByType(DocumentLineTypes::TYPE_GOOD) as $line) {
                    if ($line->getSaleItem() === $subject) {
                        $quantity += $line->getQuantity();
                    }
                }
            }

            return $quantity;
        }

        // Discount line case
        if ($subject instanceof SaleAdjustmentInterface) {
            $sale = $subject->getSale();
            if (!$sale instanceof Invoice\InvoiceSubjectInterface) {
                return 0;
            }

            $quantity = 0;
            foreach ($sale->getInvoices() as $invoice) {
                if (Invoice\InvoiceTypes::isCredit($invoice) || $invoice === $ignore) {
                    continue;
                }

                foreach ($invoice->getLinesByType(DocumentLineTypes::TYPE_DISCOUNT) as $line) {
                    if ($line->getSaleAdjustment() === $subject) {
                        $quantity += $line->getQuantity();
                    }
                }
            }

            return $quantity;
        }

        // Shipment line case
        if ($subject instanceof SaleInterface) {
            if (!$subject instanceof Invoice\InvoiceSubjectInterface) {
                return 0;
            }

            $quantity = 0;
            foreach ($subject->getInvoices() as $invoice) {
                if (Invoice\InvoiceTypes::isCredit($invoice) || $invoice === $ignore) {
                    continue;
                }

                foreach ($invoice->getLinesByType(DocumentLineTypes::TYPE_SHIPMENT) as $line) {
                    $quantity += $line->getQuantity();
                }
            }

            return $quantity;
        }

        throw new InvalidArgumentException(sprintf(
            "Unexpected instance of %s, %s or %s",
            SaleInterface::class,
            SaleItemInterface::class,
            SaleAdjustmentInterface::class
        ));
    }

    /**
     * @inheritdoc
     */
    public function calculateCreditedQuantity($subject, Invoice\InvoiceInterface $ignore = null)
    {
        // Good line case
        if ($subject instanceof SaleItemInterface) {
            $sale = $subject->getSale();
            if (!$sale instanceof Invoice\InvoiceSubjectInterface) {
                return 0;
            }

            $quantity = 0;
            foreach ($sale->getInvoices() as $invoice) {
                if (!Invoice\InvoiceTypes::isCredit($invoice) || $invoice === $ignore) {
                    continue;
                }

                foreach ($invoice->getLinesByType(DocumentLineTypes::TYPE_GOOD) as $line) {
                    if ($line->getSaleItem() === $subject) {
                        $quantity += $line->getQuantity();
                    }
                }
            }

            return $quantity;
        }

        // Discount line case
        if ($subject instanceof SaleAdjustmentInterface) {
            $sale = $subject->getSale();
            if (!$sale instanceof Invoice\InvoiceSubjectInterface) {
                return 0;
            }

            $quantity = 0;
            foreach ($sale->getInvoices() as $invoice) {
                if (!Invoice\InvoiceTypes::isCredit($invoice) || $invoice === $ignore) {
                    continue;
                }

                foreach ($invoice->getLinesByType(DocumentLineTypes::TYPE_DISCOUNT) as $line) {
                    if ($line->getSaleAdjustment() === $subject) {
                        $quantity += $line->getQuantity();
                    }
                }
            }

            return $quantity;
        }

        // Shipment line case
        if ($subject instanceof SaleInterface) {
            if (!$subject instanceof Invoice\InvoiceSubjectInterface) {
                return 0;
            }

            $quantity = 0;
            foreach ($subject->getInvoices() as $invoice) {
                if (!Invoice\InvoiceTypes::isCredit($invoice) || $invoice === $ignore) {
                    continue;
                }

                foreach ($invoice->getLinesByType(DocumentLineTypes::TYPE_SHIPMENT) as $line) {
                    $quantity += $line->getQuantity();
                }
            }

            return $quantity;
        }

        throw new InvalidArgumentException(sprintf(
            "Unexpected instance of %s, %s or %s",
            SaleInterface::class,
            SaleItemInterface::class,
            SaleAdjustmentInterface::class
        ));
    }

    /**
     * @inheritdoc
     */
    public function calculateInvoiceTotal(Invoice\InvoiceSubjectInterface $subject)
    {
        $total = .0;

        foreach ($subject->getInvoices() as $invoice) {
            if (Invoice\InvoiceTypes::isInvoice($invoice)) {
                $total += $invoice->getGrandTotal();
            }
        }

        return $total;
    }

    /**
     * @inheritdoc
     */
    public function calculateCreditTotal(Invoice\InvoiceSubjectInterface $subject)
    {
        $total = .0;

        foreach ($subject->getInvoices() as $invoice) {
            if (Invoice\InvoiceTypes::isCredit($invoice)) {
                $total += $invoice->getGrandTotal();
            }
        }

        return $total;
    }

    /**
     * @inheritdoc
     */
    public function buildInvoiceQuantityMap(Invoice\InvoiceSubjectInterface $subject)
    {
        $quantities = [];

        if ($subject instanceof SaleInterface) {
            foreach ($subject->getItems() as $item) {
                $this->buildSaleItemQuantities($item, $quantities);
            }
        }

        return $quantities;
    }

    /**
     * Builds the sale item quantities recursively.
     *
     * @param SaleItemInterface $item
     * @param array             $quantities
     */
    private function buildSaleItemQuantities(SaleItemInterface $item, array &$quantities)
    {
        // Skip compound with only public children
        if (!($item->isCompound() && !$item->hasPrivateChildren())) {
            $quantities[$item->getId()] = [
                'total'    => $item->getTotalQuantity(),
                'invoiced' => $this->calculateInvoicedQuantity($item),
                'credited' => $this->calculateCreditedQuantity($item),
            ];
        }

        if ($item->hasChildren()) {
            foreach ($item->getChildren() as $child) {
                $this->buildSaleItemQuantities($child, $quantities);
            }
        }
    }
}
