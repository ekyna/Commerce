<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Invoice\Calculator;

use Decimal\Decimal;
use Ekyna\Component\Commerce\Common\Currency\CurrencyConverterInterface;
use Ekyna\Component\Commerce\Common\Model\SaleAdjustmentInterface as Adjustment;
use Ekyna\Component\Commerce\Common\Model\SaleInterface as Sale;
use Ekyna\Component\Commerce\Common\Model\SaleItemInterface as Item;
use Ekyna\Component\Commerce\Common\Util\Money;
use Ekyna\Component\Commerce\Document\Model\DocumentLineTypes;
use Ekyna\Component\Commerce\Exception\UnexpectedTypeException;
use Ekyna\Component\Commerce\Invoice\Model\InvoiceInterface as Invoice;
use Ekyna\Component\Commerce\Invoice\Model\InvoiceSubjectInterface as Subject;
use Ekyna\Component\Commerce\Shipment\Calculator\ShipmentSubjectCalculatorInterface;

/**
 * Class InvoiceSubjectCalculator
 * @package Ekyna\Component\Commerce\Invoice\Calculator
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class InvoiceSubjectCalculator implements InvoiceSubjectCalculatorInterface
{
    protected CurrencyConverterInterface $currencyConverter;
    protected ShipmentSubjectCalculatorInterface $shipmentCalculator;

    public function __construct(CurrencyConverterInterface $converter)
    {
        $this->currencyConverter = $converter;
    }

    public function setShipmentCalculator(ShipmentSubjectCalculatorInterface $shipmentCalculator): void
    {
        $this->shipmentCalculator = $shipmentCalculator;
    }

    /**
     * @inheritDoc
     */
    public function isInvoiced($itemOrAdjustment): bool
    {
        if ($itemOrAdjustment instanceof Item) {
            // If compound with only public children
            if ($itemOrAdjustment->isCompound() && !$itemOrAdjustment->hasPrivateChildren()) {
                // Invoiced if any of its children is
                foreach ($itemOrAdjustment->getChildren() as $child) {
                    if ($this->isInvoiced($child)) {
                        return true;
                    }
                }

                return false;
            }

            $sale = $itemOrAdjustment->getSale();
            if (!$sale instanceof Subject) {
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

        if ($itemOrAdjustment instanceof Adjustment) {
            $sale = $itemOrAdjustment->getSale();
            if (!$sale instanceof Subject) {
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
            Item::class,
            Adjustment::class,
        ]);
    }

    /**
     * @inheritDoc
     */
    public function calculateInvoiceableQuantity($subject, Invoice $ignore = null): Decimal
    {
        // Good line case
        if ($subject instanceof Item) {
            $sale = $subject->getSale();
            if (!$sale instanceof Subject) {
                return new Decimal(0);
            }

            // Quantity = Total - Invoiced (ignoring current invoice) - Credited
            $quantity = $subject->getTotalQuantity()
                - $this->calculateInvoicedQuantity($subject, $ignore)
                + $this->calculateCreditedQuantity($subject);

            return max(new Decimal(0), $quantity);
        }

        // Discount line case
        if ($subject instanceof Adjustment) {
            $sale = $subject->getSale();
            if (!$sale instanceof Subject) {
                return new Decimal(0);
            }

            // Discounts must be dispatched into all invoices
            return new Decimal(1);
        }

        // Shipment line case
        if ($subject instanceof Sale) {
            if (!$subject instanceof Subject) {
                return new Decimal(0);
            }

            // Quantity = 1 - Invoiced (ignoring current invoice) - Credited
            $quantity = 1
                - $this->calculateInvoicedQuantity($subject, $ignore)
                + $this->calculateCreditedQuantity($subject);

            // Shipment must be invoiced once
            return min(new Decimal(1), max(new Decimal(0), $quantity));
        }

        throw new UnexpectedTypeException($subject, [
            Sale::class,
            Item::class,
            Adjustment::class,
        ]);
    }

    /**
     * @inheritDoc
     */
    public function calculateCreditableQuantity($subject, Invoice $ignore = null): Decimal
    {
        // Good line case
        if ($subject instanceof Item) {
            $sale = $subject->getSale();
            if (!$sale instanceof Subject) {
                return new Decimal(0);
            }

            // Quantity = Invoiced - Credited (ignoring current credit)
            $quantity = $this->calculateInvoicedQuantity($subject)
                      - $this->calculateCreditedQuantity($subject, $ignore);

            return max(new Decimal(0), $quantity);
        }

        // Discount line case
        if ($subject instanceof Adjustment) {
            $sale = $subject->getSale();
            if (!$sale instanceof Subject) {
                return new Decimal(0);
            }

            // Discounts must be dispatched into all invoices
            return new Decimal(1);
        }

        // Shipment line case
        if ($subject instanceof Sale) {
            if (!$subject instanceof Subject) {
                return new Decimal(0);
            }

            // Shipment can be credited once
            return max(new Decimal(1), $this->calculateInvoicedQuantity($subject));
        }

        throw new UnexpectedTypeException($subject, [
            Sale::class,
            Item::class,
            Adjustment::class,
        ]);
    }

    /**
     * @inheritDoc
     */
    public function calculateInvoicedQuantity($subject, Invoice $ignore = null): Decimal
    {
        return $this->calculateQuantity($subject, false, $ignore);
    }

    /**
     * @inheritDoc
     */
    public function calculateCreditedQuantity($subject, Invoice $ignore = null, bool $adjustment = null): Decimal
    {
        return $this->calculateQuantity($subject, true, $ignore, $adjustment);
    }

    /**
     * @inheritDoc
     */
    public function calculateSoldQuantity($subject): Decimal
    {
        // Good line case
        if ($subject instanceof Item) {
            $sale = $subject->getSale();
            if (!$sale instanceof Subject) {
                return $subject->getTotalQuantity();
            }
            $base = $subject->getTotalQuantity();
        }
        // Discount line case
        elseif ($subject instanceof Adjustment) {
            $sale = $subject->getSale();
            if (!$sale instanceof Subject) {
                return new Decimal(1);
            }
            $base = new Decimal(1);
        }
        // Shipment line case
        elseif ($subject instanceof Sale) {
            if (!$subject instanceof Subject) {
                return new Decimal(1);
            }
            $base = new Decimal(1);
        } else {
            throw new UnexpectedTypeException($subject, [
                Sale::class,
                Item::class,
                Adjustment::class,
            ]);
        }

        $max = max(
            $base,
            $this->calculateInvoicedQuantity($subject) - $this->calculateCreditedQuantity($subject, null, true)
        );

        return $max - $this->calculateCreditedQuantity($subject, null, false);
    }

    public function buildInvoiceQuantityMap(Subject $subject): array
    {
        $quantities = [];

        if ($subject instanceof Sale) {
            foreach ($subject->getItems() as $item) {
                $this->buildSaleItemQuantities($item, $quantities);
            }
        }

        return $quantities;
    }

    public function calculateInvoiceTotal(Subject $subject, string $currency = null): Decimal
    {
        return $this->calculateTotal($subject, false, $currency);
    }

    public function calculateCreditTotal(Subject $subject, string $currency = null): Decimal
    {
        return $this->calculateTotal($subject, true, $currency);
    }

    /**
     * Calculates the given subject's quantity.
     *
     * @param Sale|Item|Adjustment $subject
     * @param bool                 $credit
     * @param Invoice|null         $ignore
     * @param bool                 $adjustment TRUE: only adjustments, FALSE: exclude adjustments and NULL: all credits
     *
     * @return Decimal
     */
    private function calculateQuantity(
        $subject,
        bool $credit = false,
        Invoice $ignore = null,
        bool $adjustment = null
    ): Decimal {
        // Good line case
        if ($subject instanceof Item) {
            $sale = $subject->getSale();
            if (!$sale instanceof Subject) {
                return new Decimal(0);
            }

            if ($subject->isCompound()) {
                $quantity = new Decimal(INF);
                foreach ($subject->getChildren() as $child) {
                    $cQty = $this->calculateQuantity($child, $credit, $ignore, $adjustment) / $child->getQuantity();
                    $quantity = min($quantity, $cQty);
                }

                return $quantity;
            }

            $quantity = new Decimal(0);
            foreach ($sale->getInvoices(!$credit) as $invoice) {
                if ($invoice === $ignore) {
                    continue;
                }

                if ($credit && !is_null($adjustment) && ($adjustment xor $invoice->isIgnoreStock())) {
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
        if ($subject instanceof Adjustment) {
            $sale = $subject->getSale();
            if (!$sale instanceof Subject) {
                return new Decimal(0);
            }

            $quantity = new Decimal(0);
            foreach ($sale->getInvoices(!$credit) as $invoice) {
                if ($invoice === $ignore) {
                    continue;
                }

                if ($credit && !is_null($adjustment) && ($adjustment xor $invoice->isIgnoreStock())) {
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
        if ($subject instanceof Sale) {
            if (!$subject instanceof Subject) {
                return new Decimal(0);
            }

            $quantity = new Decimal(0);
            foreach ($subject->getInvoices(!$credit) as $invoice) {
                if ($invoice === $ignore) {
                    continue;
                }

                if ($credit && !is_null($adjustment) && ($adjustment xor $invoice->isIgnoreStock())) {
                    continue;
                }

                foreach ($invoice->getLinesByType(DocumentLineTypes::TYPE_SHIPMENT) as $line) {
                    $quantity += $line->getQuantity();
                }
            }

            return $quantity;
        }

        throw new UnexpectedTypeException($subject, [
            Sale::class,
            Item::class,
            Adjustment::class,
        ]);
    }

    /**
     * Calculates the total of all subject's invoices or credits.
     */
    private function calculateTotal(Subject $subject, bool $credit, string $currency = null): Decimal
    {
        $currency = $currency ?? $this->currencyConverter->getDefaultCurrency();

        $total = new Decimal(0);

        foreach ($subject->getInvoices(!$credit) as $invoice) {
            $total += $this->getAmount($invoice, $currency);
        }

        return $total;
    }

    /**
     * Builds the sale item quantities recursively.
     */
    private function buildSaleItemQuantities(Item $item, array &$quantities): void
    {
        // Skip compound with only public children
        if (!($item->isCompound() && !$item->hasPrivateChildren())) {
            $quantities[$item->getId()] = [
                'total'    => $item->getTotalQuantity(),
                'invoiced' => $this->calculateInvoicedQuantity($item),
                'adjusted' => $this->calculateCreditedQuantity($item, null, true),
                'credited' => $this->calculateCreditedQuantity($item, null, false),
                'shipped'  => $this->shipmentCalculator->calculateShippedQuantity($item),
                'returned' => $this->shipmentCalculator->calculateReturnedQuantity($item),
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
     */
    protected function getAmount(Invoice $invoice, string $currency): Decimal
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
