<?php

namespace Ekyna\Component\Commerce\Invoice\Resolver;

use Ekyna\Component\Commerce\Common\Currency\CurrencyConverterInterface;
use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Common\Util\Combination;
use Ekyna\Component\Commerce\Common\Util\Money;
use Ekyna\Component\Commerce\Exception\RuntimeException;
use Ekyna\Component\Commerce\Invoice\Model as IM;
use Ekyna\Component\Commerce\Payment\Model as PM;

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
    public function resolve(IM\InvoiceInterface $invoice): array
    {
        if ($invoice->getType() !== IM\InvoiceTypes::TYPE_INVOICE) {
            throw new RuntimeException(sprintf("Expected invoice of type '%s'.", IM\InvoiceTypes::TYPE_INVOICE));
        }

        $id = spl_object_id($invoice);

        if (!isset($this->cache[$id])) {
            $this->buildInvoicePayments($invoice->getSale());
        }

        return $this->cache[$id];
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
    private function buildInvoicePayments(SaleInterface $sale): void
    {
        $this->buildPaymentList($sale);
        /** @var IM\InvoiceSubjectInterface $sale */
        $this->buildInvoiceList($sale);

        // Creates cache entries for each invoices
        foreach ($this->invoices as $invoice) {
            $this->cache[spl_object_id($invoice['invoice'])] = [];
        }

        // First pass: payment <-> invoices combination exact match lookup
        foreach ($this->payments as $p => &$payment) {
            foreach (Combination::generateAssoc($this->invoices) as $combination) {
                $sum = array_sum(array_map(function ($i) {
                    return $i['total'];
                }, $combination));

                if (0 !== Money::compare($sum, $payment['amount'], $this->currency)) {
                    continue;
                }

                $this->buildInvoicePayment(array_keys($combination), [$p]);

                continue 2;
            }
        }

        if (empty($this->payments)) {
            return;
        }

        // Second pass: fills invoices with payments by creation date
        foreach ($this->invoices as $i => &$invoice) {
            foreach ($this->payments as $p => &$payment) {
                $this->buildInvoicePayment([$i], [$p]);

                unset($payment);

                if (!isset($this->invoices[$i])) {
                    break;
                }
            }

            unset($invoice);

            if (empty($this->payments)) {
                break;
            }
        }
    }

    /**
     * @param array $is The invoices keys
     * @param array $ps The payments keys
     */
    private function buildInvoicePayment(array $is, array $ps)
    {
        foreach ($is as $i) {
            $oid = spl_object_id($this->invoices[$i]['invoice']);

            foreach ($ps as $p) {
                $result = new IM\InvoicePayment();
                $result->setPayment($this->payments[$p]['payment']);

                // TODO sale currency
                $c = Money::compare($this->invoices[$i]['total'], $this->payments[$p]['amount'], $this->currency);

                if (0 === $c) { // Equal
                    $result->setAmount($this->payments[$p]['amount']);
                    $result->setRealAmount($this->payments[$p]['real_amount']);
                    $this->invoices[$i]['total'] = 0;
                    $this->invoices[$i]['real_total'] = 0;
                    $this->payments[$p]['amount'] = 0;
                    $this->payments[$p]['real_amount'] = 0;
                    unset($this->invoices[$i]);
                    unset($this->payments[$p]);
                } elseif (1 === $c) { // invoice > payment
                    $result->setAmount($this->payments[$p]['amount']);
                    $result->setRealAmount($this->payments[$p]['real_amount']);
                    $this->invoices[$i]['total'] -= $this->payments[$p]['amount'];
                    $this->invoices[$i]['real_total'] -= $this->payments[$p]['real_amount'];
                    $this->payments[$p]['amount'] = 0;
                    $this->payments[$p]['real_amount'] = 0;
                    unset($this->payments[$p]);
                } else { // payment > invoice
                    $result->setAmount($this->invoices[$i]['total']);
                    $result->setRealAmount($this->invoices[$i]['real_total']);
                    $this->payments[$p]['amount'] -= $this->invoices[$i]['total'];
                    $this->payments[$p]['real_amount'] -= $this->invoices[$i]['real_total'];
                    $this->invoices[$i]['total'] = 0;
                    $this->invoices[$i]['real_total'] = 0;
                    unset($this->invoices[$i]);
                }

                $this->cache[$oid][] = $result;
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
        // Get all invoices but credits
        $invoices = $subject->getInvoices(true)->toArray();

        // Sort invoices by date ASC
        usort($invoices, function (IM\InvoiceInterface $a, IM\InvoiceInterface $b) {
            return $a->getCreatedAt()->getTimestamp() - $b->getCreatedAt()->getTimestamp();
        });

        // Build invoices list
        $this->invoices = array_map(function (IM\InvoiceInterface $invoice) {
            return [
                'invoice'    => $invoice,
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
        // TODO Deal with refund payments when implemented
        // Get all payments but outstanding or not paid
        $payments = array_filter($subject->getPayments()->toArray(), function (PM\PaymentInterface $p) {
            if ($p->getMethod()->isOutstanding()) {
                return false;
            }

            if (!PM\PaymentStates::isPaidState($p->getState())) {
                return false;
            }

            return true;
        });

        // Sort payments by date ASC
        usort($payments, function (PM\PaymentInterface $a, PM\PaymentInterface $b) {
            return $a->getCompletedAt()->getTimestamp() - $b->getCompletedAt()->getTimestamp();
        });

        // Build payments list by converting amounts into default currency using sale's exchange rate
        $this->payments = array_map(function (PM\PaymentInterface $payment) use ($subject) {
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

            return [
                'payment'     => $payment,
                'amount'      => $amount,
                'real_amount' => $realAmount,
            ];
        }, $payments);
    }
}
