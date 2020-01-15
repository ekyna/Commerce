<?php

namespace Ekyna\Component\Commerce\Customer\Balance;

use Ekyna\Component\Commerce\Common\Currency\CurrencyConverterInterface;
use Ekyna\Component\Commerce\Exception\LogicException;
use Ekyna\Component\Commerce\Order\Model\OrderInvoiceInterface;
use Ekyna\Component\Commerce\Order\Model\OrderPaymentInterface;
use Ekyna\Component\Commerce\Order\Repository\OrderInvoiceRepositoryInterface;
use Ekyna\Component\Commerce\Order\Repository\OrderPaymentRepositoryInterface;
use Ekyna\Component\Commerce\Payment\Model\PaymentStates;
use Ekyna\Component\Commerce\Payment\Resolver\DueDateResolverInterface;

/**
 * Class BalanceBuilder
 * @package Ekyna\Component\Commerce\Customer\Balance
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class BalanceBuilder
{
    /**
     * @var OrderInvoiceRepositoryInterface
     */
    protected $invoiceRepository;

    /**
     * @var OrderPaymentRepositoryInterface
     */
    protected $paymentRepository;

    /**
     * @var CurrencyConverterInterface
     */
    protected $currencyConverter;

    /**
     * @var DueDateResolverInterface
     */
    protected $dueDateResolver;


    /**
     * Constructor.
     *
     * @param OrderInvoiceRepositoryInterface $invoiceRepository
     * @param OrderPaymentRepositoryInterface $paymentRepository
     * @param CurrencyConverterInterface      $currencyConverter
     * @param DueDateResolverInterface        $dueDateResolver
     */
    public function __construct(
        OrderInvoiceRepositoryInterface $invoiceRepository,
        OrderPaymentRepositoryInterface $paymentRepository,
        CurrencyConverterInterface $currencyConverter,
        DueDateResolverInterface $dueDateResolver
    ) {
        $this->invoiceRepository = $invoiceRepository;
        $this->paymentRepository = $paymentRepository;
        $this->currencyConverter = $currencyConverter;
        $this->dueDateResolver   = $dueDateResolver;
    }

    /**
     * Builds the customer balance.
     *
     * @param Balance $balance
     */
    public function build(Balance $balance): void
    {
        $payments = [];

        if ($balance->getFilter() === Balance::FILTER_DUE_INVOICES) {
            $invoices = $this->invoiceRepository->findDueInvoices($balance->getCustomer(), $balance->getCurrency());
        } elseif ($balance->getFilter() === Balance::FILTER_BEFALL_INVOICES) {
            $invoices = $this->invoiceRepository->findFallInvoices($balance->getCustomer(), $balance->getCurrency());
        } else {
            $invoices = $this
                ->invoiceRepository
                ->findByCustomerAndDateRange($balance->getCustomer(), $balance->getCurrency(), null, $balance->getTo());

            $payments = $this
                ->paymentRepository
                ->findByCustomerAndDateRange($balance->getCustomer(), $balance->getCurrency(), null, $balance->getTo());
        }

        $this->buildInvoices($balance, $invoices);
        $this->buildPayments($balance, $payments);

        $balance->sortLines();
    }

    /**
     * Builds the invoices lines.
     *
     * @param Balance                 $balance
     * @param OrderInvoiceInterface[] $invoices
     */
    private function buildInvoices(Balance $balance, array $invoices): void
    {
        foreach ($invoices as $invoice) {
            $debit   = $credit = 0;
            $dueDate = null;

            if ($invoice->isCredit()) {
                $type   = Line::TYPE_CREDIT;
                $credit = $invoice->getGrandTotal();
            } else {
                $type    = Line::TYPE_INVOICE;
                $debit   = $invoice->getGrandTotal();
                $dueDate = $invoice->getDueDate();

                if ($balance->getFilter() !== Balance::FILTER_ALL) {
                    $credit = $invoice->getPaidTotal();
                }
            }

            if ($balance->getFrom() && $balance->getFrom()->getTimestamp() > $invoice->getCreatedAt()->getTimestamp()) {
                if ($balance->getFilter() === Balance::FILTER_ALL) {
                    $balance->addCreditForward($credit);
                    $balance->addDebitForward($debit);
                }
            } else {
                $order = $invoice->getOrder();

                $isDue = $this->dueDateResolver->isInvoiceDue($invoice);

                $line = new Line(
                    $invoice->getCreatedAt(),
                    $type,
                    $invoice->getNumber(),
                    $debit,
                    $credit,
                    $order->getId(),
                    $order->getNumber(),
                    (string)$order->getVoucherNumber(),
                    $order->getCreatedAt(),
                    $isDue ? $dueDate : null
                );

                $line->setDue($isDue);

                $balance->addLine($line);
            }
        }
    }

    /**
     * Builds the payments lines.
     *
     * @param Balance                 $balance
     * @param OrderPaymentInterface[] $payments
     */
    private function buildPayments(Balance $balance, array $payments): void
    {
        foreach ($payments as $payment) {
            $bc = $balance->getCurrency();
            $pc = $payment->getCurrency()->getCode();

            if ($pc === $bc) {
                $amount = $payment->getAmount();
            } elseif ($pc === $this->currencyConverter->getDefaultCurrency()) {
                $amount = $this
                    ->currencyConverter
                    ->convertWithRate($payment->getAmount(), $payment->getSale()->getExchangeRate(), $bc);
            } else {
                throw new LogicException("Unexpected payment currency");
            }

            $debit = $credit = 0;
            if ($payment->isRefund()) {
                $type  = Line::TYPE_REFUND;
                $debit = $amount;
            } else {
                $type   = Line::TYPE_PAYMENT;
                $credit = $amount;
            }

            if ($balance->getFrom() && $balance->getFrom()->getTimestamp() > $payment->getCreatedAt()->getTimestamp()) {
                if ($balance->getFilter() === Balance::FILTER_ALL) {
                    $balance->addCreditForward($credit);
                    $balance->addDebitForward($debit);

                    // Refunded payment
                    if (!$payment->isRefund() && $payment->getState() === PaymentStates::STATE_REFUNDED) {
                        $balance->addCreditForward($debit);
                        $balance->addDebitForward($credit);
                    }
                }

                continue;
            }

            $order = $payment->getOrder();

            $line = new Line(
                $payment->getCreatedAt(),
                $type,
                $payment->getNumber(),
                $debit,
                $credit,
                $order->getId(),
                $order->getNumber(),
                (string)$order->getVoucherNumber(),
                $order->getCreatedAt()
            );

            $balance->addLine($line);

            // Refunded payment
            if (!$payment->isRefund() && $payment->getState() === PaymentStates::STATE_REFUNDED) {
                $line = new Line(
                    $payment->getCreatedAt(),
                    Line::TYPE_REFUND,
                    $payment->getNumber(),
                    $credit,
                    $debit,
                    $order->getId(),
                    $order->getNumber(),
                    (string)$order->getVoucherNumber(),
                    $order->getCreatedAt()
                );

                $balance->addLine($line);
            }
        }
    }
}
