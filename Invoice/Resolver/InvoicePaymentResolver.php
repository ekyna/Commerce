<?php

namespace Ekyna\Component\Commerce\Invoice\Resolver;

use Ekyna\Component\Commerce\Common\Currency\CurrencyConverterInterface;
use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Common\Util\Combination;
use Ekyna\Component\Commerce\Common\Util\Money;
use Ekyna\Component\Commerce\Exception\RuntimeException;
use Ekyna\Component\Commerce\Invoice\Model as IM;
use Ekyna\Component\Commerce\Payment\Model as PM;

use function array_filter;
use function array_keys;
use function array_map;
use function array_sum;
use function spl_object_id;

/**
 * Class InvoicePaymentResolver
 * @package Ekyna\Component\Commerce\Invoice\Resolver
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class InvoicePaymentResolver implements InvoicePaymentResolverInterface
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
     * @var array
     */
    protected $cache;

    /**
     * @var array
     */
    private $payments;

    /**
     * @var array
     */
    private $invoices;


    /**
     * Constructor.
     *
     * @param CurrencyConverterInterface $currencyConverter
     */
    public function __construct(CurrencyConverterInterface $currencyConverter)
    {
        $this->currencyConverter = $currencyConverter;
        $this->currency = $currencyConverter->getDefaultCurrency();

        $this->clear();
    }

    /**
     * Clears the cache.
     */
    public function clear()
    {
        $this->cache = [];
    }

    /**
     * @inheritDoc
     *
     * @return IM\InvoicePayment[]
     */
    public function resolve(IM\InvoiceInterface $invoice, bool $invoices = true): array
    {
        $id = spl_object_id($invoice);

        if (!isset($this->cache[$id])) {
            $this->build($invoice->getSale());
        }

        if ($invoices) {
            return $this->cache[$id];
        }

        return array_filter($this->cache[$id], function (IM\InvoicePayment $p) {
            return null !== $p->getPayment();
        });
    }

    /**
     * @inheritDoc
     */
    public function getPaidTotal(IM\InvoiceInterface $invoice): float
    {
        $payments = $this->resolve($invoice);

        $total = 0;
        foreach ($payments as $payment) {
            $total += $payment->getAmount();
        }

        return $total;
    }

    /**
     * @inheritDoc
     */
    public function getRealPaidTotal(IM\InvoiceInterface $invoice): float
    {
        $payments = $this->resolve($invoice);

        $total = 0;
        foreach ($payments as $payment) {
            $total += $payment->getRealAmount();
        }

        return $total;
    }

    /**
     * Builds invoice payments for the given sale.
     *
     * @param SaleInterface $sale
     */
    private function build(SaleInterface $sale): void
    {
        $this->buildPaymentList($sale);
        /** @var IM\InvoiceSubjectInterface $sale */
        $this->buildInvoiceList($sale);

        // Creates cache entries for each invoices
        foreach ($this->invoices as $invoice) {
            $this->cache[spl_object_id($invoice['invoice'])] = [];
        }

        // Combining too many invoices use too much resources
        if (12 < count($this->invoices)) {
            $this->buildPaymentsResults(array_keys($this->invoices), array_keys($this->payments));

            return;
        }

        // First pass: payment <-> invoices combination exact match lookup
        foreach ($this->payments as $p => $payment) {
            $invoices = array_filter($this->invoices, function ($i) use ($payment) {
                return ($payment['refund'] === $i['credit']) && 0 < $i['total'];
            });

            $sum = array_sum(array_map(function ($i) {
                return $i['total'];
            }, $invoices));

            // If payment amount is greater or equals invoices sum
            if (1 === Money::compare($payment['amount'], $sum, $this->currency)) {
                $this->buildPaymentsResults(array_keys($invoices), [$p]);
                continue;
            }

            $combinations = Combination::generateAssoc($invoices);
            foreach ($combinations as $combination) {
                $sum = array_sum(array_map(function ($i) {
                    return $i['total'];
                }, $combination));

                if (0 !== Money::compare($sum, $payment['amount'], $this->currency)) {
                    continue;
                }

                $this->buildPaymentsResults(array_keys($combination), [$p]);

                continue 2;
            }
        }

        // Second pass: invoice <-> payments combination exact match lookup
        foreach ($this->invoices as $i => &$invoice) {
            // Skip paid invoices
            if (0 >= $invoice['total']) {
                continue;
            }

            $payments = array_filter($this->payments, function ($p) use ($invoice) {
                return ($invoice['credit'] === $p['refund']) && 0 < $p['amount'];
            });

            $sum = array_sum(array_map(function ($i) {
                return $i['amount'];
            }, $payments));

            // If invoice total is greater or equals payments sum
            if (1 === Money::compare($invoice['total'], $sum, $this->currency)) {
                $this->buildPaymentsResults([$i], array_keys($payments));
                continue;
            }

            $combinations = Combination::generateAssoc($payments);
            foreach ($combinations as $combination) {
                $sum = array_sum(array_map(function ($p) {
                    return $p['amount'];
                }, $combination));

                if (0 !== Money::compare($sum, $invoice['total'], $this->currency)) {
                    continue;
                }

                $this->buildPaymentsResults([$i], array_keys($combination));

                continue 2;
            }
        }

        // Third pass: fills invoices with payments by creation date
        foreach ($this->invoices as $i => &$invoice) {
            // Skip paid invoices
            if (0 >= $invoice['total']) {
                continue;
            }

            foreach (array_keys($this->payments) as $p) {
                $this->buildPaymentsResults([$i], [$p]);

                if (0 >= $invoice['total']) {
                    break;
                }
            }
        }

        // Third pass: cancel invoices with credits (and vice versa) combination exact match lookup
        foreach ($this->invoices as $i => &$invoice) {
            // Skip paid invoices
            if (0 >= $invoice['total']) {
                continue;
            }

            // Filter others invoices, credits for invoice (and vice versa)
            $others = array_filter($this->invoices, function ($i) use ($invoice) {
                return $invoice['invoice'] !== $i['invoice']
                    && $invoice['credit'] !== $i['credit']
                    && 0 < $i['total'];
            });

            $sum = array_sum(array_map(function ($i) {
                return $i['total'];
            }, $others));

            // If invoice total is greater or equals others sum
            if (1 === Money::compare($invoice['total'], $sum, $this->currency)) {
                $this->buildInvoiceResults([$i], array_keys($others));
                continue;
            }

            $combinations = Combination::generateAssoc($others);
            foreach ($combinations as $combination) {
                $sum = array_sum(array_map(function ($i) {
                    return $i['total'];
                }, $combination));

                if (0 !== Money::compare($sum, $invoice['total'], $this->currency)) {
                    continue;
                }

                $this->buildInvoiceResults([$i], array_keys($combination));

                continue 2;
            }
        }

        // Fourth pass: fills invoices with credits (and vice versa) by creation date
        foreach ($this->invoices as $i => &$invoice) {
            // Skip paid invoices/credits
            if (0 >= $invoice['total']) {
                continue;
            }

            // Filter others invoices, credits for invoices (and vice versa)
            $others = array_filter($this->invoices, function ($i) use ($invoice) {
                return $invoice['invoice'] !== $i['invoice']
                    && $invoice['credit'] !== $i['credit']
                    && 0 < $i['total'];
            });

            foreach (array_keys($others) as $o) {
                $this->buildInvoiceResults([$i], [$o]);

                if (0 >= $invoice['total']) {
                    break;
                }
            }
        }

        if (empty($this->payments)) {
            return;
        }

        // Purge remaining payments/refunds
        foreach ($this->payments as $k1 => &$p1) {
            foreach ($this->payments as $k2 => &$p2) {
                // Skip same payments
                if ($k1 === $k2) {
                    continue;
                }

                // Use payment with refund (and vice versa)
                if (!($p1['refund'] xor $p2['refund'])) {
                    continue;
                }

                $c = Money::compare($p1['real_amount'], $p2['real_amount'], $this->currency);

                if (0 === $c) {
                    unset($this->payments[$k2]);
                    unset($this->payments[$k1]);
                    continue 2;
                }

                if (1 === $c) {
                    $p1['amount'] -= $p2['amount'];
                    $p1['real_amount'] -= $p2['real_amount'];
                    unset($this->payments[$k2]);
                    continue;
                }

                $p2['amount'] -= $p1['amount'];
                $p2['real_amount'] -= $p1['real_amount'];
                unset($this->payments[$k1]);
                continue 2;
            }
        }

        if (empty($this->payments)) {
            return;
        }

        // Fifth pass: fills invoices with refunds
        foreach ($this->invoices as $i => &$invoice) {
            if ($invoice['credit']) {
                continue;
            }

            // Filter payments : refunds for invoices and payments for credits
            $refunds = array_filter($this->payments, function ($p) use ($invoice) {
                return $p['refund'];
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
            $oid = spl_object_id($this->invoices[$i]['invoice']);

            foreach ($ps as $p) {
                if (!isset($this->payments[$p])) {
                    continue;
                }

                if (0 >= $this->invoices[$i]['total']) {
                    break;
                }

                // Browse payments for invoices, and refunds for credits
                if ($this->invoices[$i]['credit'] xor $this->payments[$p]['refund']) {
                    continue;
                }

                $result = new IM\InvoicePayment();
                $result->setPayment($this->payments[$p]['payment']);

                // TODO sale currency
                // TODO What if invoice and payment currencies differs ?
                $c = Money::compare($this->invoices[$i]['total'], $this->payments[$p]['amount'], $this->currency);

                if (0 > $c) { // payment > invoice
                    $amount = $this->invoices[$i]['total'];
                    $real = $this->invoices[$i]['real_total'];
                } else { // invoice >= payment
                    $amount = $this->payments[$p]['amount'];
                    $real = $this->payments[$p]['real_amount'];
                }

                $result->setAmount($amount);
                $result->setRealAmount($real);

                $this->invoices[$i]['total'] -= $amount;
                $this->invoices[$i]['real_total'] -= $real;
                $this->payments[$p]['amount'] -= $amount;
                $this->payments[$p]['real_amount'] -= $real;

                $this->cache[$oid][] = $result;

                /*if (0 === Money::compare(0, $this->invoices[$i]['total'], $this->currency)) {
                    unset($this->invoices[$i]);
                }*/
                if (0 === Money::compare(0, $this->payments[$p]['amount'], $this->currency)) {
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
            $iOid = spl_object_id($this->invoices[$i]['invoice']);

            foreach ($os as $o) {
                if (0 >= $this->invoices[$o]['total']) {
                    continue;
                }
                if (0 >= $this->invoices[$i]['total']) {
                    break;
                }

                $oOid = spl_object_id($this->invoices[$o]['invoice']);

                // Browse credits for invoices
                if ($this->invoices[$i]['credit'] xor !$this->invoices[$o]['credit']) {
                    continue;
                }

                $c = Money::compare($this->invoices[$i]['total'], $this->invoices[$o]['total'], $this->currency);

                if (0 > $c) { // other > invoice
                    $total = $this->invoices[$i]['total'];
                    $real = $this->invoices[$i]['real_total'];
                } else { // invoice >= other
                    $total = $this->invoices[$o]['total'];
                    $real = $this->invoices[$o]['real_total'];
                }

                $iResult = new IM\InvoicePayment();
                $iResult->setInvoice($this->invoices[$o]['invoice']);
                $iResult->setAmount($total);
                $iResult->setRealAmount($real);

                $oResult = new IM\InvoicePayment();
                $oResult->setInvoice($this->invoices[$i]['invoice']);
                $oResult->setAmount($total);
                $oResult->setRealAmount($real);

                $this->invoices[$o]['total'] -= $total;
                $this->invoices[$o]['real_total'] -= $real;
                $this->invoices[$i]['total'] -= $total;
                $this->invoices[$i]['real_total'] -= $real;

                /*if (0 === Money::compare(0, $this->invoices[$i]['total'], $this->currency)) {
                    unset($this->invoices[$i]);
                }
                if (0 === Money::compare(0, $this->invoices[$o]['total'], $this->currency)) {
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
            $oid = spl_object_id($this->invoices[$i]['invoice']);

            foreach ($ps as $p) {
                if (!$this->payments[$p]['refund']) {
                    continue;
                }

                $result = new IM\InvoicePayment();
                $result->setPayment($this->payments[$p]['payment']);

                /** @var IM\InvoiceInterface $invoice */
                $invoice = $this->invoices[$i]['invoice'];
                $remaining = $invoice->getGrandTotal() - $this->invoices[$i]['total'];

                // TODO sale currency
                // TODO What if invoice and payment currencies differs ?
                $c = Money::compare($remaining, $this->payments[$p]['amount'], $this->currency);

                if (0 > $c) { // payment > remaining
                    $amount = $remaining;
                    $real = $invoice->getRealGrandTotal() - $this->invoices[$i]['real_total'];
                } else { // remaining >= payment
                    $amount = $this->payments[$p]['amount'];
                    $real = $this->payments[$p]['real_amount'];
                }

                $result->setAmount(-$amount);
                $result->setRealAmount(-$real);

                $this->invoices[$i]['total'] += $amount;
                $this->invoices[$i]['real_total'] += $real;
                $this->payments[$p]['amount'] -= $amount;
                $this->payments[$p]['real_amount'] -= $real;

                $this->cache[$oid][] = $result;

                if (0 === Money::compare(0, $this->payments[$p]['amount'], $this->currency)) {
                    unset($this->payments[$p]);
                }
            }
        }
    }

    /**
     * Builds the sale invoices list.
     *
     * @param IM\InvoiceSubjectInterface $subject
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
            return [
                'invoice'    => $invoice,
                'credit'     => $invoice->isCredit(),
                'total'      => $invoice->getGrandTotal(),
                'real_total' => $invoice->getRealGrandTotal(),
            ];
        }, $invoices);
    }

    /**
     * Builds the sale payments list.
     *
     * @param PM\PaymentSubjectInterface $subject
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
                throw new RuntimeException("Unexpected payment currency.");
            }

            $rate = $this->currencyConverter->getSubjectExchangeRate($subject, $pc, $sc);
            $amount = $this->currencyConverter->convertWithRate($payment->getAmount(), $rate, $sc);

            $rate = $this->currencyConverter->getSubjectExchangeRate($subject, $pc, $this->currency);
            $realAmount = $this->currencyConverter->convertWithRate($payment->getAmount(), $rate, $this->currency);

            $this->payments[] = [
                'payment'     => $payment,
                'refund'      => $payment->isRefund(),
                'amount'      => $amount,
                'real_amount' => $realAmount,
            ];

            if (!$payment->isRefund() && $payment->getState() === PM\PaymentStates::STATE_REFUNDED) {
                $this->payments[] = [
                    'payment'     => $payment,
                    'refund'      => true,
                    'amount'      => $amount,
                    'real_amount' => $realAmount,
                ];
            }
        }
    }
}
