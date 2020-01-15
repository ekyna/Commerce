<?php

namespace Ekyna\Component\Commerce\Invoice\Calculator;

use Ekyna\Component\Commerce\Common\Currency\CurrencyConverterInterface;
use Ekyna\Component\Commerce\Common\Model\SaleAdjustmentInterface;
use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Common\Model\SaleItemInterface;
use Ekyna\Component\Commerce\Common\Util\Money;
use Ekyna\Component\Commerce\Document\Model\DocumentLineTypes;
use Ekyna\Component\Commerce\Exception\UnexpectedTypeException;
use Ekyna\Component\Commerce\Invoice\Model as Invoice;

/**
 * Class InvoiceSubjectCalculator
 * @package Ekyna\Component\Commerce\Invoice\Calculator
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class InvoiceSubjectCalculator implements InvoiceSubjectCalculatorInterface
{
    /**
     * @var CurrencyConverterInterface
     */
    protected $currencyConverter;

    /**
     * @var string
     */
    protected $currency;


    /**
     * Constructor.
     *
     * @param CurrencyConverterInterface $converter
     */
    public function __construct(CurrencyConverterInterface $converter)
    {
        $this->currencyConverter = $converter;
        $this->currency          = $converter->getDefaultCurrency();
    }

    /**
     * @inheritdoc
     */
    public function isInvoiced($itemOrAdjustment): bool
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

        throw new UnexpectedTypeException($itemOrAdjustment, [
            SaleItemInterface::class,
            SaleAdjustmentInterface::class,
        ]);
    }

    /**
     * @inheritdoc
     */
    public function calculateInvoiceableQuantity($subject, Invoice\InvoiceInterface $ignore = null): float
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

        throw new UnexpectedTypeException($subject, [
            SaleInterface::class,
            SaleItemInterface::class,
            SaleAdjustmentInterface::class,
        ]);
    }

    /**
     * @inheritdoc
     */
    public function calculateCreditableQuantity($subject, Invoice\InvoiceInterface $ignore = null): float
    {
        // Good line case
        if ($subject instanceof SaleItemInterface) {
            $sale = $subject->getSale();
            if (!$sale instanceof Invoice\InvoiceSubjectInterface) {
                return 0;
            }

            // Quantity = Invoiced - Credited (ignoring current credit)
            $quantity = $this->calculateInvoicedQuantity($subject)
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

        throw new UnexpectedTypeException($subject, [
            SaleInterface::class,
            SaleItemInterface::class,
            SaleAdjustmentInterface::class,
        ]);
    }

    /**
     * @inheritdoc
     */
    public function calculateInvoicedQuantity($subject, Invoice\InvoiceInterface $ignore = null): float
    {
        return $this->calculateQuantity($subject, false, $ignore);
    }

    /**
     * @inheritdoc
     */
    public function calculateCreditedQuantity($subject, Invoice\InvoiceInterface $ignore = null): float
    {
        return $this->calculateQuantity($subject, true, $ignore);
    }

    /**
     * Calculates the given subject's quantity.
     *
     * @param SaleInterface|SaleItemInterface|SaleAdjustmentInterface $subject
     * @param bool                                                    $credit
     * @param Invoice\InvoiceInterface                                $ignore
     *
     * @return float
     */
    private function calculateQuantity($subject, bool $credit = false, Invoice\InvoiceInterface $ignore = null): float
    {
        // Good line case
        if ($subject instanceof SaleItemInterface) {
            $sale = $subject->getSale();
            if (!$sale instanceof Invoice\InvoiceSubjectInterface) {
                return 0;
            }

            $quantity = 0;
            foreach ($sale->getInvoices(!$credit) as $invoice) {
                if ($invoice === $ignore) {
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
            foreach ($sale->getInvoices(!$credit) as $invoice) {
                if ($invoice === $ignore) {
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
            foreach ($subject->getInvoices(!$credit) as $invoice) {
                if ($invoice === $ignore) {
                    continue;
                }

                foreach ($invoice->getLinesByType(DocumentLineTypes::TYPE_SHIPMENT) as $line) {
                    $quantity += $line->getQuantity();
                }
            }

            return $quantity;
        }

        throw new UnexpectedTypeException($subject, [
            SaleInterface::class,
            SaleItemInterface::class,
            SaleAdjustmentInterface::class,
        ]);
    }

    /**
     * @inheritdoc
     */
    public function calculateInvoiceTotal(Invoice\InvoiceSubjectInterface $subject, string $currency = null): float
    {
        return $this->calculateTotal($subject, false, $currency);
    }

    /**
     * @inheritdoc
     */
    public function calculateCreditTotal(Invoice\InvoiceSubjectInterface $subject, string $currency = null): float
    {
        return $this->calculateTotal($subject, true, $currency);
    }

    /**
     * Calculates the total of all subject's invoices or credits.
     *
     * @param Invoice\InvoiceSubjectInterface $subject
     * @param bool                            $credit
     * @param string|null                     $currency
     *
     * @return float
     */
    private function calculateTotal(
        Invoice\InvoiceSubjectInterface $subject,
        bool $credit,
        string $currency = null
    ): float {
        $currency = $currency ?? $this->currency;

        $total = .0;

        foreach ($subject->getInvoices(!$credit) as $invoice) {
            $total += $this->getAmount($invoice, $currency);
        }

        return $total;
    }

    /**
     * @inheritdoc
     */
    public function buildInvoiceQuantityMap(Invoice\InvoiceSubjectInterface $subject): array
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
    private function buildSaleItemQuantities(SaleItemInterface $item, array &$quantities): void
    {
        // Skip compound with only public children
        if (!($item->isCompound() && !$item->hasPrivateChildren())) {
            $quantities[$item->getId()] = [
                'total' => $item->getTotalQuantity(),
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

    /**
     * Returns the payment amount in the given currency.
     *
     * @param Invoice\InvoiceInterface $invoice
     * @param string                   $currency
     *
     * @return float
     */
    protected function getAmount(Invoice\InvoiceInterface $invoice, string $currency): float
    {
        $ic = $invoice->getCurrency();

        if ($currency === $ic) {
            return Money::round($invoice->getGrandTotal(), $currency);
        }

        $rate = $this
            ->currencyConverter
            ->getSubjectExchangeRate($invoice->getSale(), $ic, $currency);

        return $this
            ->currencyConverter
            ->convertWithRate($invoice->getGrandTotal(), $rate, $currency);
    }
}
