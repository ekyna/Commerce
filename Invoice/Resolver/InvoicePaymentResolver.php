<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Invoice\Resolver;

use Decimal\Decimal;
use Ekyna\Component\Commerce\Common\Currency\CurrencyConverterInterface;
use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Common\Util\Combination;
use Ekyna\Component\Commerce\Exception\RuntimeException;
use Ekyna\Component\Commerce\Invoice\Model as IM;
use Ekyna\Component\Commerce\Payment\Model as PM;

use function array_filter;
use function array_keys;
use function array_map;
use function array_unique;
use function count;
use function in_array;
use function usort;

/**
 * Class InvoicePaymentResolver
 * @package Ekyna\Component\Commerce\Invoice\Resolver
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class InvoicePaymentResolver implements InvoicePaymentResolverInterface
{
    protected CurrencyConverterInterface $currencyConverter;
    protected string                     $currency;
    protected array                      $cache;
    /** @var array<int, PaymentData> */
    private array $payments;
    /** @var array<int, InvoiceData> */
    private array $invoices;

    public function __construct(CurrencyConverterInterface $currencyConverter)
    {
        $this->currencyConverter = $currencyConverter;
        $this->currency = $currencyConverter->getDefaultCurrency();

        $this->clear();
    }

    public function clear(): void
    {
        $this->cache = [];
    }

    public function resolve(IM\InvoiceInterface $invoice, bool $invoices = true): array
    {
        $uid = $invoice->getRuntimeUid();

        if (!isset($this->cache[$uid])) {
            $this->build($invoice->getSale());
        }

        if ($invoices) {
            return $this->cache[$uid];
        }

        return array_filter($this->cache[$uid], function (IM\InvoicePayment $p) {
            return null !== $p->getPayment();
        });
    }

    public function getPaidTotal(IM\InvoiceInterface $invoice): Decimal
    {
        $payments = $this->resolve($invoice);

        $total = new Decimal(0);
        foreach ($payments as $payment) {
            $total += $payment->getAmount();
        }

        return $total;
    }

    public function getRealPaidTotal(IM\InvoiceInterface $invoice): Decimal
    {
        $payments = $this->resolve($invoice);

        $total = new Decimal(0);
        foreach ($payments as $payment) {
            $total += $payment->getRealAmount();
        }

        return $total;
    }

    /**
     * Builds invoice payments for the given sale.
     */
    private function build(SaleInterface $sale): void
    {
        $this->buildPaymentList($sale);
        /** @var IM\InvoiceSubjectInterface $sale */
        $this->buildInvoiceList($sale);

        // Creates cache entries for each invoices
        foreach ($this->invoices as $invoice) {
            $this->cache[$invoice->invoice->getRuntimeUid()] = [];
        }

        // Combining too many invoices use too much resources
        if (12 < count($this->invoices)) {
            $this->buildPaymentsResults(array_keys($this->invoices), array_keys($this->payments));

            return;
        }

        // First pass: payment <-> invoices combination exact match lookup
        foreach ($this->payments as $p => $payment) {
            $invoices = array_filter($this->invoices, function ($i) use ($payment) {
                return ($payment->refund === $i->credit) && 0 < $i->total;
            });

            $sum = Decimal::sum(array_map(function ($i) {
                return $i->total;
            }, $invoices));

            // If payment amount is greater or equals invoices sum
            if ($payment->amount >= $sum) {
                $this->buildPaymentsResults(array_keys($invoices), [$p]);
                continue;
            }

            $combinations = Combination::generateAssoc($invoices);
            foreach ($combinations as $combination) {
                $sum = Decimal::sum(array_map(function ($i) {
                    return $i->total;
                }, $combination));

                if (!$payment->amount->equals($sum)) {
                    continue;
                }

                $this->buildPaymentsResults(array_keys($combination), [$p]);

                continue 2;
            }
        }

        // Second pass: invoice <-> payments combination exact match lookup
        foreach ($this->invoices as $i => $invoice) {
            // Skip paid invoices
            if (0 >= $invoice->total) {
                continue;
            }

            $payments = array_filter($this->payments, function ($p) use ($invoice) {
                return ($invoice->credit === $p->refund) && 0 < $p->amount;
            });

            $sum = Decimal::sum(array_map(function ($i) {
                return $i->amount;
            }, $payments));

            // If invoice total is greater or equals payments sum
            if ($invoice->total >= $sum) {
                $this->buildPaymentsResults([$i], array_keys($payments));
                continue;
            }

            $combinations = Combination::generateAssoc($payments);
            foreach ($combinations as $combination) {
                $sum = Decimal::sum(array_map(function ($p) {
                    return $p->amount;
                }, $combination));

                if (!$invoice->total->equals($sum)) {
                    continue;
                }

                $this->buildPaymentsResults([$i], array_keys($combination));

                continue 2;
            }
        }

        // Third pass: fills invoices with payments by creation date
        foreach ($this->invoices as $i => $invoice) {
            // Skip paid invoices
            if (0 >= $invoice->total) {
                continue;
            }

            foreach (array_keys($this->payments) as $p) {
                $this->buildPaymentsResults([$i], [$p]);

                if (0 >= $invoice->total) {
                    break;
                }
            }
        }

        // Third pass: cancel invoices with credits (and vice versa) combination exact match lookup
        foreach ($this->invoices as $i => $invoice) {
            // Skip paid invoices
            if (0 >= $invoice->total) {
                continue;
            }

            // Filter others invoices, credits for invoice (and vice versa)
            $others = array_filter($this->invoices, function ($i) use ($invoice) {
                return $invoice->invoice !== $i->invoice
                    && $invoice->credit !== $i->credit
                    && 0 < $i->total;
            });

            $sum = Decimal::sum(array_map(function ($i) {
                return $i->total;
            }, $others));

            // If invoice total is greater or equals others sum
            if ($invoice->total > $sum) {
                $this->buildInvoiceResults([$i], array_keys($others));
                continue;
            }

            $combinations = Combination::generateAssoc($others);
            foreach ($combinations as $combination) {
                $sum = Decimal::sum(array_map(function ($i) {
                    return $i->total;
                }, $combination));

                if (!$invoice->total->equals($sum)) {
                    continue;
                }

                $this->buildInvoiceResults([$i], array_keys($combination));

                continue 2;
            }
        }

        // Fourth pass: fills invoices with credits (and vice versa) by creation date
        foreach ($this->invoices as $i => $invoice) {
            // Skip paid invoices/credits
            if (0 >= $invoice->total) {
                continue;
            }

            // Filter others invoices, credits for invoices (and vice versa)
            $others = array_filter($this->invoices, function ($i) use ($invoice) {
                return $invoice->invoice !== $i->invoice
                    && $invoice->credit !== $i->credit
                    && 0 < $i->total;
            });

            foreach (array_keys($others) as $o) {
                $this->buildInvoiceResults([$i], [$o]);

                if (0 >= $invoice->total) {
                    break;
                }
            }
        }

        if (empty($this->payments)) {
            return;
        }

        // Purge remaining payments/refunds
        foreach ($this->payments as $k1 => $p1) {
            foreach ($this->payments as $k2 => $p2) {
                // Skip same payments
                if ($k1 === $k2) {
                    continue;
                }

                // Use payment with refund (and vice versa)
                if (!($p1->refund xor $p2->refund)) {
                    continue;
                }

                $c = $p1->realAmount->compareTo($p2->realAmount);

                if (0 === $c) {
                    unset($this->payments[$k2]);
                    unset($this->payments[$k1]);
                    continue 2;
                }

                if (1 === $c) {
                    $p1->amount -= $p2->amount;
                    $p1->realAmount -= $p2->realAmount;
                    unset($this->payments[$k2]);
                    continue;
                }

                $p2->amount -= $p1->amount;
                $p2->realAmount -= $p1->realAmount;
                unset($this->payments[$k1]);
                continue 2;
            }
        }

        if (empty($this->payments)) {
            return;
        }

        // Fifth pass: fills invoices with refunds
        foreach ($this->invoices as $i => $invoice) {
            if ($invoice->credit) {
                continue;
            }

            // Filter payments : refunds for invoices and payments for credits
            $refunds = array_filter($this->payments, function ($p) use ($invoice) {
                return $p->refund;
            });

            foreach (array_keys($refunds) as $r) {
                $this->buildRefundResults([$i], [$r]);

                if (!isset($this->invoices[$i])) {
                    break;
                }
            }

            unset($this->invoices[$i]);
        }
    }

    /**
     * @param array $is The invoices keys
     * @param array $ps The payments keys
     */
    private function buildPaymentsResults(array $is, array $ps)
    {
        foreach ($is as $i) {
            $oid = $this->invoices[$i]->invoice->getRuntimeUid();

            foreach ($ps as $p) {
                if (!isset($this->payments[$p])) {
                    continue;
                }

                if (0 >= $this->invoices[$i]->total) {
                    break;
                }

                // Browse payments for invoices, and refunds for credits
                if ($this->invoices[$i]->credit xor $this->payments[$p]->refund) {
                    continue;
                }

                $result = new IM\InvoicePayment();
                $result->setPayment($this->payments[$p]->payment);

                // TODO sale currency
                // TODO What if invoice and payment currencies differs ?
                $c = $this->invoices[$i]->total->compareTo($this->payments[$p]->amount);

                if (0 > $c) { // payment > invoice
                    $amount = $this->invoices[$i]->total;
                    $real = $this->invoices[$i]->realTotal;
                } else { // invoice >= payment
                    $amount = $this->payments[$p]->amount;
                    $real = $this->payments[$p]->realAmount;
                }

                $result->setAmount($amount);
                $result->setRealAmount($real);

                $this->invoices[$i]->total -= $amount;
                $this->invoices[$i]->realTotal -= $real;
                $this->payments[$p]->amount -= $amount;
                $this->payments[$p]->realAmount -= $real;

                $this->cache[$oid][] = $result;

                /*if ($this->invoices[$i]->total->isZero()) {
                    unset($this->invoices[$i]);
                }*/
                if ($this->payments[$p]->amount->isZero()) {
                    unset($this->payments[$p]);
                }
            }
        }
    }

    /**
     * @param array $is The invoices keys
     * @param array $os The other invoices keys
     */
    private function buildInvoiceResults(array $is, array $os): void
    {
        foreach ($is as $i) {
            $iOid = $this->invoices[$i]->invoice->getRuntimeUid();

            foreach ($os as $o) {
                if (0 >= $this->invoices[$o]->total) {
                    continue;
                }
                if (0 >= $this->invoices[$i]->total) {
                    break;
                }

                $oOid = $this->invoices[$o]->invoice->getRuntimeUid();

                // Browse credits for invoices
                if ($this->invoices[$i]->credit xor !$this->invoices[$o]->credit) {
                    continue;
                }

                $c = $this->invoices[$i]->total->compareTo($this->invoices[$o]->total);

                if (0 > $c) { // other > invoice
                    $total = $this->invoices[$i]->total;
                    $real = $this->invoices[$i]->realTotal;
                } else { // invoice >= other
                    $total = $this->invoices[$o]->total;
                    $real = $this->invoices[$o]->realTotal;
                }

                $iResult = new IM\InvoicePayment();
                $iResult->setInvoice($this->invoices[$o]->invoice);
                $iResult->setAmount($total);
                $iResult->setRealAmount($real);

                $oResult = new IM\InvoicePayment();
                $oResult->setInvoice($this->invoices[$i]->invoice);
                $oResult->setAmount($total);
                $oResult->setRealAmount($real);

                $this->invoices[$o]->total -= $total;
                $this->invoices[$o]->realTotal -= $real;
                $this->invoices[$i]->total -= $total;
                $this->invoices[$i]->realTotal -= $real;

                /*if ($this->invoices[$i]->total->isZero()) {
                    unset($this->invoices[$i]);
                }
                if ($this->invoices[$o]->total->isZero()) {
                    unset($this->invoices[$o]);
                }*/

                $this->cache[$iOid][] = $iResult;
                $this->cache[$oOid][] = $oResult;
            }
        }
    }

    /**
     * @param array $is The invoices keys
     * @param array $ps The payments keys
     */
    private function buildRefundResults(array $is, array $ps)
    {
        foreach ($is as $i) {
            $oid = $this->invoices[$i]->invoice->getRuntimeUid();

            foreach ($ps as $p) {
                if (!$this->payments[$p]->refund) {
                    continue;
                }

                $result = new IM\InvoicePayment();
                $result->setPayment($this->payments[$p]->payment);

                /** @var IM\InvoiceInterface $invoice */
                $invoice = $this->invoices[$i]->invoice;
                $remaining = $invoice->getGrandTotal() - $this->invoices[$i]->total;

                // TODO sale currency
                // TODO What if invoice and payment currencies differs ?
                $c = $remaining->compareTo($this->payments[$p]->amount);

                if (0 > $c) { // payment > remaining
                    $amount = $remaining;
                    $real = $invoice->getRealGrandTotal() - $this->invoices[$i]->realTotal;
                } else { // remaining >= payment
                    $amount = $this->payments[$p]->amount;
                    $real = $this->payments[$p]->realAmount;
                }

                $result->setAmount($amount->negate());
                $result->setRealAmount($real->negate());

                $this->invoices[$i]->total += $amount;
                $this->invoices[$i]->realTotal += $real;
                $this->payments[$p]->amount -= $amount;
                $this->payments[$p]->realAmount -= $real;

                $this->cache[$oid][] = $result;

                if ($this->payments[$p]->amount->isZero()) {
                    unset($this->payments[$p]);
                }
            }
        }
    }

    /**
     * Builds the sale invoices list.
     */
    private function buildInvoiceList(IM\InvoiceSubjectInterface $subject): void
    {
        // Get all invoices and credits
        $invoices = $subject->getInvoices()->toArray();

        // Sort invoices by date ASC
        usort($invoices, function (IM\InvoiceInterface $a, IM\InvoiceInterface $b) {
            return $a->getCreatedAt()->getTimestamp() - $b->getCreatedAt()->getTimestamp();
        });

        // Build invoices list
        $this->invoices = array_map(function (IM\InvoiceInterface $invoice) {
            return new InvoiceData(
                $invoice,
                $invoice->isCredit(),
                $invoice->getGrandTotal(),
                $invoice->getRealGrandTotal(),
            );
        }, $invoices);
    }

    /**
     * Builds the sale payments list.
     */
    private function buildPaymentList(PM\PaymentSubjectInterface $subject): void
    {
        // Get all payments but outstanding or not paid
        $payments = array_filter($subject->getPayments()->toArray(), function (PM\PaymentInterface $p) {
            if ($p->getMethod()->isOutstanding()) {
                return false;
            }

            if (!PM\PaymentStates::isCompletedState($p, true)) {
                return false;
            }

            return true;
        });

        // Sort payments by date ASC
        usort($payments, function (PM\PaymentInterface $a, PM\PaymentInterface $b) {
            return $a->getCompletedAt()->getTimestamp() - $b->getCompletedAt()->getTimestamp();
        });

        $this->payments = [];

        // Build payments list by converting amounts into default currency using sale's exchange rate
        /** @var PM\PaymentInterface $payment */
        foreach ($payments as $payment) {
            $sc = $subject->getCurrency()->getCode();
            $pc = $payment->getCurrency()->getCode();

            // Payment currency must be either sale's currency or the default currency
            if (!in_array($pc, array_unique([$sc, $this->currency]), true)) {
                throw new RuntimeException('Unexpected payment currency.');
            }

            $rate = $this->currencyConverter->getSubjectExchangeRate($subject, $pc, $sc);
            $amount = $this->currencyConverter->convertWithRate($payment->getAmount(), $rate, $sc);

            $rate = $this->currencyConverter->getSubjectExchangeRate($subject, $pc, $this->currency);
            $realAmount = $this->currencyConverter->convertWithRate($payment->getAmount(), $rate, $this->currency);

            $this->payments[] = new PaymentData($payment, $payment->isRefund(), $amount, $realAmount);

            if (!$payment->isRefund() && $payment->getState() === PM\PaymentStates::STATE_REFUNDED) {
                $this->payments[] = new PaymentData($payment, true, $amount, $realAmount);
            }
        }
    }
}
