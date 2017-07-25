<?php

namespace Ekyna\Component\Commerce\Invoice\Util;

use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Exception\LogicException;
use Ekyna\Component\Commerce\Invoice\Model;

/**
 * Class InvoiceUtil
 * @package Ekyna\Component\Commerce\Invoice
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
abstract class InvoiceUtil
{
    /**
     * Calculate the item's max credit quantity.
     *
     * @param Model\InvoiceLineInterface $line
     *
     * @return float
     *
     * @throws LogicException
     */
    static public function calculateMaxCreditQuantity(Model\InvoiceLineInterface $line)
    {
        if ($line->getInvoice()->getType() !== Model\InvoiceTypes::TYPE_CREDIT) {
            throw new LogicException(sprintf("Expected invoice with type '%s'.", Model\InvoiceTypes::TYPE_CREDIT));
        }

        if (null === $sale = $line->getInvoice()->getSale()) {
            throw new LogicException("Invoice's sale must be set.");
        }

        $quantity = 0;

        if ($line->getType() === Model\InvoiceLineTypes::TYPE_GOOD) {
            if (null === $saleItem = $line->getSaleItem()) {
                throw new LogicException("Invoice line's sale item must be set.");
            }

            foreach ($sale->getInvoices() as $invoice) {
                // Ignore the current item's invoice
                if ($invoice === $line->getInvoice()) {
                    continue;
                }

                foreach ($invoice->getLines() as $invoiceLine) {
                    if ($invoiceLine->getType() !== Model\InvoiceLineTypes::TYPE_GOOD) {
                        continue;
                    }

                    if ($invoiceLine->getSaleItem() === $saleItem) {
                        if (Model\InvoiceTypes::TYPE_INVOICE === $invoice->getType()) {
                            $quantity += $invoiceLine->getQuantity();
                        } elseif (Model\InvoiceTypes::TYPE_CREDIT === $invoice->getType()) {
                            $quantity -= $invoiceLine->getQuantity();
                        }
                    }
                }
            }
        } elseif ($line->getType() === Model\InvoiceLineTypes::TYPE_DISCOUNT) {
            if (null === $adjustment = $line->getSaleAdjustment()) {
                throw new LogicException("Invoice line's sale adjustment must be set.");
            }

            foreach ($sale->getInvoices() as $invoice) {
                // Ignore the current item's invoice
                if ($invoice === $line->getInvoice()) {
                    continue;
                }

                foreach ($invoice->getLines() as $invoiceLine) {
                    if ($invoiceLine->getType() !== Model\InvoiceLineTypes::TYPE_DISCOUNT) {
                        continue;
                    }

                    if ($invoiceLine->getSaleAdjustment() === $adjustment) {
                        if (Model\InvoiceTypes::TYPE_INVOICE === $invoice->getType()) {
                            $quantity += $invoiceLine->getQuantity();
                        } elseif (Model\InvoiceTypes::TYPE_CREDIT === $invoice->getType()) {
                            $quantity -= $invoiceLine->getQuantity();
                        }
                    }
                }
            }
        } elseif ($line->getType() === Model\InvoiceLineTypes::TYPE_SHIPMENT) {
            foreach ($sale->getInvoices() as $invoice) {
                // Ignore the current item's invoice
                if ($invoice === $line->getInvoice()) {
                    continue;
                }

                foreach ($invoice->getLines() as $invoiceLine) {
                    if ($invoiceLine->getType() !== Model\InvoiceLineTypes::TYPE_SHIPMENT) {
                        continue;
                    }

                    if (Model\InvoiceTypes::TYPE_INVOICE === $invoice->getType()) {
                        $quantity += $invoiceLine->getQuantity();
                    } elseif (Model\InvoiceTypes::TYPE_CREDIT === $invoice->getType()) {
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
     * Calculate the item's max invoice quantity.
     *
     * @param Model\InvoiceLineInterface $line
     *
     * @return float
     *
     * @throws LogicException
     */
    static public function calculateMaxInvoiceQuantity(Model\InvoiceLineInterface $line)
    {
        if ($line->getInvoice()->getType() !== Model\InvoiceTypes::TYPE_INVOICE) {
            throw new LogicException(sprintf("Expected invoice with type '%s'.", Model\InvoiceTypes::TYPE_INVOICE));
        }

        if (null === $sale = $line->getInvoice()->getSale()) {
            throw new LogicException("Invoice's sale must be set.");
        }

        if ($line->getType() === Model\InvoiceLineTypes::TYPE_GOOD) {
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
                    if ($invoiceLine->getType() !== Model\InvoiceLineTypes::TYPE_GOOD) {
                        continue;
                    }

                    if ($invoiceLine->getSaleItem() === $saleItem) {
                        if (Model\InvoiceTypes::TYPE_INVOICE === $invoice->getType()) {
                            $quantity -= $invoiceLine->getQuantity();
                        } elseif (Model\InvoiceTypes::TYPE_CREDIT === $invoice->getType()) {
                            $quantity += $invoiceLine->getQuantity();
                        }
                    }
                }
            }
        } elseif ($line->getType() === Model\InvoiceLineTypes::TYPE_DISCOUNT) {
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
                    if ($invoiceLine->getType() !== Model\InvoiceLineTypes::TYPE_DISCOUNT) {
                        continue;
                    }

                    if ($invoiceLine->getSaleAdjustment() === $adjustment) {
                        if (Model\InvoiceTypes::TYPE_INVOICE === $invoice->getType()) {
                            $quantity -= $invoiceLine->getQuantity();
                        } elseif (Model\InvoiceTypes::TYPE_CREDIT === $invoice->getType()) {
                            $quantity += $invoiceLine->getQuantity();
                        }
                    }
                }
            }
        } elseif ($line->getType() === Model\InvoiceLineTypes::TYPE_SHIPMENT) {
            $quantity = 1;

            foreach ($sale->getInvoices() as $invoice) {
                // Ignore the current item's invoice
                if ($invoice === $line->getInvoice()) {
                    continue;
                }

                foreach ($invoice->getLines() as $invoiceLine) {
                    if ($invoiceLine->getType() !== Model\InvoiceLineTypes::TYPE_SHIPMENT) {
                        continue;
                    }

                    if (Model\InvoiceTypes::TYPE_INVOICE === $invoice->getType()) {
                        $quantity -= $invoiceLine->getQuantity();
                    } elseif (Model\InvoiceTypes::TYPE_CREDIT === $invoice->getType()) {
                        $quantity += $invoiceLine->getQuantity();
                    }
                }
            }
        } else {
            throw new InvalidArgumentException("Unexpected line type '{$line->getType()}'.");
        }

        return $quantity;
    }
}
