<?php

namespace Ekyna\Component\Commerce\Invoice\Calculator;

use Ekyna\Component\Commerce\Common\Model as Common;
use Ekyna\Component\Commerce\Document\Model\DocumentLineTypes;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Exception\LogicException;
use Ekyna\Component\Commerce\Invoice\Model as Invoice;

/**
 * Class QuantityCalculator
 * @package Ekyna\Component\Commerce\Invoice\Calculator
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class InvoiceCalculator implements InvoiceCalculatorInterface
{
    /**
     * @inheritDoc
     */
    public function calculateInvoiceableQuantity(Invoice\InvoiceLineInterface $line)
    {
        if ($line->getInvoice()->getType() !== Invoice\InvoiceTypes::TYPE_INVOICE) {
            throw new LogicException(sprintf("Expected invoice with type '%s'.", Invoice\InvoiceTypes::TYPE_INVOICE));
        }

        if (null === $sale = $line->getSale()) {
            throw new LogicException("Invoice's sale must be set.");
        }

        if (!$sale instanceof Invoice\InvoiceSubjectInterface) {
            throw new InvalidArgumentException("Expected instance of " . Invoice\InvoiceSubjectInterface::class);
        }

        if ($line->getType() === DocumentLineTypes::TYPE_GOOD) {
            if (null === $saleItem = $line->getSaleItem()) {
                throw new LogicException("Invoice line's sale item must be set.");
            }

            // Base quantity is the sale item total quantity.
            $quantity = $saleItem->getTotalQuantity();

            // Debit invoice's sale item quantities
            foreach ($sale->getInvoices() as $invoice) {
                // Ignore the current item's invoice
                if ($invoice === $line->getInvoice()) {
                    continue;
                }

                foreach ($invoice->getLines() as $invoiceLine) {
                    if ($invoiceLine->getType() !== DocumentLineTypes::TYPE_GOOD) {
                        continue;
                    }

                    if ($invoiceLine->getSaleItem() === $saleItem) {
                        if (Invoice\InvoiceTypes::TYPE_INVOICE === $invoice->getType()) {
                            $quantity -= $invoiceLine->getQuantity();
                        } elseif (Invoice\InvoiceTypes::TYPE_CREDIT === $invoice->getType()) {
                            $quantity += $invoiceLine->getQuantity();
                        }
                    }
                }
            }
        } elseif ($line->getType() === DocumentLineTypes::TYPE_DISCOUNT) {
            if (null === $adjustment = $line->getSaleAdjustment()) {
                throw new LogicException("Invoice line's sale adjustment must be set.");
            }

            $quantity = 1;

            foreach ($sale->getInvoices() as $invoice) {
                // Ignore the current item's invoice
                if ($invoice === $line->getInvoice()) {
                    continue;
                }

                foreach ($invoice->getLines() as $invoiceLine) {
                    if ($invoiceLine->getType() !== DocumentLineTypes::TYPE_DISCOUNT) {
                        continue;
                    }

                    if ($invoiceLine->getSaleAdjustment() === $adjustment) {
                        if (Invoice\InvoiceTypes::TYPE_INVOICE === $invoice->getType()) {
                            $quantity -= $invoiceLine->getQuantity();
                        } elseif (Invoice\InvoiceTypes::TYPE_CREDIT === $invoice->getType()) {
                            $quantity += $invoiceLine->getQuantity();
                        }
                    }
                }
            }
        } elseif ($line->getType() === DocumentLineTypes::TYPE_SHIPMENT) {
            $quantity = 1;

            foreach ($sale->getInvoices() as $invoice) {
                // Ignore the current item's invoice
                if ($invoice === $line->getInvoice()) {
                    continue;
                }

                foreach ($invoice->getLines() as $invoiceLine) {
                    if ($invoiceLine->getType() !== DocumentLineTypes::TYPE_SHIPMENT) {
                        continue;
                    }

                    if (Invoice\InvoiceTypes::TYPE_INVOICE === $invoice->getType()) {
                        $quantity -= $invoiceLine->getQuantity();
                    } elseif (Invoice\InvoiceTypes::TYPE_CREDIT === $invoice->getType()) {
                        $quantity += $invoiceLine->getQuantity();
                    }
                }
            }
        } else {
            throw new InvalidArgumentException("Unexpected line type '{$line->getType()}'.");
        }

        return $quantity;
    }

    /**
     * @inheritDoc
     */
    public function calculateCreditableQuantity(Invoice\InvoiceLineInterface $line)
    {
        if ($line->getInvoice()->getType() !== Invoice\InvoiceTypes::TYPE_CREDIT) {
            throw new LogicException(sprintf("Expected invoice with type '%s'.", Invoice\InvoiceTypes::TYPE_CREDIT));
        }

        if (null === $sale = $line->getSale()) {
            throw new LogicException("Invoice's sale must be set.");
        }

        if (!$sale instanceof Invoice\InvoiceSubjectInterface) {
            throw new InvalidArgumentException("Expected instance of " . Invoice\InvoiceSubjectInterface::class);
        }

        $quantity = 0;

        if ($line->getType() === DocumentLineTypes::TYPE_GOOD) {
            if (null === $saleItem = $line->getSaleItem()) {
                throw new LogicException("Invoice line's sale item must be set.");
            }

            foreach ($sale->getInvoices() as $invoice) {
                // Ignore the current item's invoice
                if ($invoice === $line->getInvoice()) {
                    continue;
                }

                foreach ($invoice->getLines() as $invoiceLine) {
                    if ($invoiceLine->getType() !== DocumentLineTypes::TYPE_GOOD) {
                        continue;
                    }

                    if ($invoiceLine->getSaleItem() === $saleItem) {
                        if (Invoice\InvoiceTypes::TYPE_INVOICE === $invoice->getType()) {
                            $quantity += $invoiceLine->getQuantity();
                        } elseif (Invoice\InvoiceTypes::TYPE_CREDIT === $invoice->getType()) {
                            $quantity -= $invoiceLine->getQuantity();
                        }
                    }
                }
            }
        } elseif ($line->getType() === DocumentLineTypes::TYPE_DISCOUNT) {
            if (null === $adjustment = $line->getSaleAdjustment()) {
                throw new LogicException("Invoice line's sale adjustment must be set.");
            }

            foreach ($sale->getInvoices() as $invoice) {
                // Ignore the current item's invoice
                if ($invoice === $line->getInvoice()) {
                    continue;
                }

                foreach ($invoice->getLines() as $invoiceLine) {
                    if ($invoiceLine->getType() !== DocumentLineTypes::TYPE_DISCOUNT) {
                        continue;
                    }

                    if ($invoiceLine->getSaleAdjustment() === $adjustment) {
                        if (Invoice\InvoiceTypes::TYPE_INVOICE === $invoice->getType()) {
                            $quantity += $invoiceLine->getQuantity();
                        } elseif (Invoice\InvoiceTypes::TYPE_CREDIT === $invoice->getType()) {
                            $quantity -= $invoiceLine->getQuantity();
                        }
                    }
                }
            }
        } elseif ($line->getType() === DocumentLineTypes::TYPE_SHIPMENT) {
            foreach ($sale->getInvoices() as $invoice) {
                // Ignore the current item's invoice
                if ($invoice === $line->getInvoice()) {
                    continue;
                }

                foreach ($invoice->getLines() as $invoiceLine) {
                    if ($invoiceLine->getType() !== DocumentLineTypes::TYPE_SHIPMENT) {
                        continue;
                    }

                    if (Invoice\InvoiceTypes::TYPE_INVOICE === $invoice->getType()) {
                        $quantity += $invoiceLine->getQuantity();
                    } elseif (Invoice\InvoiceTypes::TYPE_CREDIT === $invoice->getType()) {
                        $quantity -= $invoiceLine->getQuantity();
                    }
                }
            }
        } else {
            throw new InvalidArgumentException("Unexpected line type '{$line->getType()}'.");
        }

        return $quantity;
    }

    /**
     * @inheritdoc
     */
    public function calculateInvoicedQuantity(Common\SaleItemInterface $item)
    {
        $sale = $item->getSale();

        if (!$sale instanceof Invoice\InvoiceSubjectInterface) {
            return 0;
        }

        $quantity = 0;

        foreach ($sale->getInvoices() as $invoice) {
            $credit = $invoice->getType() === Invoice\InvoiceTypes::TYPE_CREDIT;

            foreach ($invoice->getLinesByType(DocumentLineTypes::TYPE_GOOD) as $line) {
                if ($line->getSaleItem() === $item) {
                    $quantity += $credit ? -$line->getQuantity() : $line->getQuantity();
                }
            }
        }

        return $quantity;
    }

    /**
     * @inheritdoc
     */
    public function calculateCreditedQuantity(Common\SaleItemInterface $item)
    {
        $sale = $item->getSale();

        if (!$sale instanceof Invoice\InvoiceSubjectInterface) {
            return 0;
        }

        $quantity = 0;

        foreach ($sale->getInvoices() as $invoice) {
            if ($invoice->getType() !== Invoice\InvoiceTypes::TYPE_CREDIT) {
                continue;
            }

            foreach ($invoice->getLines() as $invoiceItem) {
                if ($invoiceItem->getSaleItem() === $item) {
                    $quantity += $invoiceItem->getQuantity();
                }
            }
        }

        return $quantity;
    }

    /**
     * @inheritdoc
     */
    public function calculateTotal(Invoice\InvoiceSubjectInterface $subject)
    {
        $total = .0;

        foreach ($subject->getInvoices() as $invoice) {
            if ($invoice->getType() === Invoice\InvoiceTypes::TYPE_CREDIT) {
                $total -=  $invoice->getGrandTotal();
            } else {
                $total +=  $invoice->getGrandTotal();
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

        if ($subject instanceof Common\SaleInterface) {
            foreach ($subject->getItems() as $item) {
                $this->buildSaleItemQuantities($item, $quantities);
            }

            // TODO Add shipment and discount quantities
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
        if (!$item->isCompound()) {
            $quantities[$item->getId()] = [
                'sold'     => $item->getTotalQuantity(),
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
