<?php

namespace Ekyna\Component\Commerce\Invoice\Resolver;

use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Common\Util\Money;
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
     * @var array
     */
    protected $cache;


    /**
     * Constructor.
     */
    public function __construct()
    {
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
     * @inheritdoc
     */
    public function resolve(IM\InvoiceInterface $invoice): array
    {
        $id = spl_object_id($invoice);

        if (!isset($this->cache[$id])) {
            $this->buildInvoicePayments($invoice->getSale());
        }

        return $this->cache[$id];
    }

    /**
     * Returns the invoice's paid total.
     *
     * @param IM\InvoiceInterface $invoice
     *
     * @return float
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
     * Builds invoice payments for the given sale.
     *
     * @param SaleInterface $sale
     */
    protected function buildInvoicePayments(SaleInterface $sale): void
    {
        $currency = $sale->getCurrency()->getCode(); // TODO Deal with currency conversions.
        $payments = $this->buildPaymentList($sale);
        /** @var IM\InvoiceSubjectInterface $sale */
        $invoices = $this->buildInvoiceList($sale);

        foreach ($invoices as $x => &$i) {
            $oid = spl_object_id($i['invoice']);
            $this->cache[$oid] = [];

            foreach ($payments as $y => &$p) {
                $r = new IM\InvoicePayment();
                $r->setPayment($p['payment']);

                $c = Money::compare($i['total'], $p['amount'], $currency);

                if (0 === $c) { // Equal
                    $r->setAmount($p['amount']);
                    $i['total'] = 0;
                    $p['amount'] = 0;
                    unset($payments[$y]);
                } elseif (1 === $c) { // invoice > payment
                    $r->setAmount($p['amount']);
                    $i['total'] -= $p['amount'];
                    $p['amount'] = 0;
                    unset($payments[$y]);
                } else { // payment > invoice
                    $r->setAmount($i['total']);
                    $p['amount'] -= $i['total'];
                    $i['total'] = 0;
                }

                $this->cache[$oid][] = $r;

                unset($p);
            }

            unset($i);
        }
    }

    /**
     * Builds the sale invoices list.
     *
     * @param IM\InvoiceSubjectInterface $subject
     *
     * @return array
     */
    protected function buildInvoiceList(IM\InvoiceSubjectInterface $subject): array
    {
        $invoices = $subject->getInvoices(true)->toArray();

        usort($invoices, function (IM\InvoiceInterface $a, IM\InvoiceInterface $b) {
            return $a->getCreatedAt()->getTimestamp() - $b->getCreatedAt()->getTimestamp();
        });

        return array_map(function (IM\InvoiceInterface $invoice) {
            return [
                'invoice' => $invoice,
                'total'   => $invoice->getGrandTotal(),
            ];
        }, $invoices);
    }

    /**
     * Builds the sale payments list.
     *
     * @param PM\PaymentSubjectInterface $subject
     *
     * @return array
     */
    protected function buildPaymentList(PM\PaymentSubjectInterface $subject): array
    {
        // TODO Deal with refund when implemented
        $payments = array_filter($subject->getPayments()->toArray(), function (PM\PaymentInterface $p) {
            if ($p->getMethod()->isOutstanding()) {
                return false;
            }

            if (!PM\PaymentStates::isPaidState($p->getState())) {
                return false;
            }

            return true;
        });

        usort($payments, function (PM\PaymentInterface $a, PM\PaymentInterface $b) {
            return $a->getCompletedAt()->getTimestamp() - $b->getCompletedAt()->getTimestamp();
        });

        // TODO Currency conversion

        return array_map(function (PM\PaymentInterface $payment) {
            return [
                'payment' => $payment,
                'amount'  => $payment->getAmount(),
            ];
        }, $payments);
    }
}
