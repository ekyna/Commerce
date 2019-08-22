<?php

namespace Ekyna\Component\Commerce\Customer\Balance;

use Ekyna\Component\Commerce\Bridge\Payum\CreditBalance\Constants as CreditBalance;
use Ekyna\Component\Commerce\Common\Currency\CurrencyConverterInterface;
use Ekyna\Component\Commerce\Common\Util\Money;
use Ekyna\Component\Commerce\Exception\LogicException;
use Ekyna\Component\Commerce\Invoice\Model\InvoiceTypes;
use Ekyna\Component\Commerce\Order\Model\OrderInvoiceInterface;
use Ekyna\Component\Commerce\Order\Model\OrderPaymentInterface;
use Ekyna\Component\Commerce\Order\Repository\OrderInvoiceRepositoryInterface;
use Ekyna\Component\Commerce\Order\Repository\OrderPaymentRepositoryInterface;
use Ekyna\Component\Commerce\Payment\Model\PaymentStates;

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
     * Constructor.
     *
     * @param OrderInvoiceRepositoryInterface $invoiceRepository
     * @param OrderPaymentRepositoryInterface $paymentRepository
     * @param CurrencyConverterInterface      $currencyConverter
     */
    public function __construct(
        OrderInvoiceRepositoryInterface $invoiceRepository,
        OrderPaymentRepositoryInterface $paymentRepository,
        CurrencyConverterInterface $currencyConverter
    ) {
        $this->invoiceRepository = $invoiceRepository;
        $this->paymentRepository = $paymentRepository;
        $this->currencyConverter = $currencyConverter;
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
            $invoices = $this->invoiceRepository->findByCustomerAndDateRange($balance->getCustomer(),
                $balance->getCurrency());
            $payments = $this->paymentRepository->findByCustomerAndDateRange($balance->getCustomer(),
                $balance->getCurrency());
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
            $debit = $credit = 0;
            $dueDate = null;

            if ($invoice->getType() === InvoiceTypes::TYPE_CREDIT) {
                $type = Line::TYPE_CREDIT;
                $credit = $invoice->getGrandTotal();
            } else {
                $type = Line::TYPE_INVOICE;
                $debit = $invoice->getGrandTotal();
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
                    $dueDate
                );

                if ($invoice->getType() === InvoiceTypes::TYPE_INVOICE) {
                    // TODO Not due if due date is future
                    // TODO Not due if there is a equivalent credit invoice :s
                    $line->setDue(
                        1 === Money::compare($invoice->getGrandTotal(), $invoice->getPaidTotal(),
                            $invoice->getCurrency())
                    );
                }

                $balance->addLine($line);
            }

            // TODO Remove whe payment refund (type) will be implemented
            if ($invoice->getType() === InvoiceTypes::TYPE_CREDIT) {
                // TODO Break dependency with bundle
                /** @var \Ekyna\Bundle\CommerceBundle\Model\PaymentMethodInterface $method */
                if (!$method = $invoice->getPaymentMethod()) {
                    continue;
                }
                if ($method->getFactoryName() !== CreditBalance::FACTORY_NAME) {
                    continue;
                }

                $debit = $invoice->getRealGrandTotal();
                $credit = 0;

                if ($balance->getFrom() && $balance->getFrom()->getTimestamp() > $invoice->getCreatedAt()->getTimestamp()) {
                    if ($balance->getFilter() === Balance::FILTER_ALL) {
                        $balance->addCreditForward($credit);
                        $balance->addDebitForward($debit);
                    }
                } else {
                    $order = $invoice->getOrder();

                    $line = new Line(
                        $invoice->getCreatedAt(),
                        Line::TYPE_REFUND,
                        $invoice->getNumber(),
                        $debit,
                        $credit,
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
            if ($payment->getState() === PaymentStates::STATE_REFUNDED) {
                $type = Line::TYPE_REFUND;
                $debit = $amount;
            } else {
                $type = Line::TYPE_PAYMENT;
                $credit = $amount;
            }

            if ($balance->getFrom() && $balance->getFrom()->getTimestamp() > $payment->getCreatedAt()->getTimestamp()) {
                if ($balance->getFilter() === Balance::FILTER_ALL) {
                    $balance->addCreditForward($credit);
                    $balance->addDebitForward($debit);

                    // TODO Remove whe payment refund (type) will be implemented
                    if ($payment->getState() === PaymentStates::STATE_REFUNDED) {
                        $balance->addCreditForward($debit);
                        $balance->addDebitForward($credit);
                    }
                }

                continue;
            }

            $order = $payment->getOrder();

            // TODO Remove whe payment refund (type) will be implemented
            // Add refund as payment first
            if ($payment->getState() === PaymentStates::STATE_REFUNDED) {
                $line = new Line(
                    $payment->getCreatedAt(),
                    Line::TYPE_PAYMENT,
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
        }
    }
}
