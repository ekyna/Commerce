<?php

namespace Ekyna\Component\Commerce\Customer\Balance;

use Ekyna\Component\Commerce\Bridge\Payum\CreditBalance\Constants as CreditBalance;
use Ekyna\Component\Commerce\Invoice\Model\InvoiceTypes;
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
     * @var array
     */
    protected $orders;


    /**
     * Constructor.
     *
     * @param OrderInvoiceRepositoryInterface $invoiceRepository
     * @param OrderPaymentRepositoryInterface $paymentRepository
     */
    public function __construct(
        OrderInvoiceRepositoryInterface $invoiceRepository,
        OrderPaymentRepositoryInterface $paymentRepository
    ) {
        $this->invoiceRepository = $invoiceRepository;
        $this->paymentRepository = $paymentRepository;
    }

    /**
     * Builds the customer balance.
     *
     * @param Balance $balance
     */
    public function build(Balance $balance): void
    {
        $this->orders = [];

        $invoices = $this
            ->invoiceRepository
            ->findByCustomerAndDateRange($balance->getCustomer(), $balance->getFrom(), $balance->getTo(), true);

        $this->buildInvoices($balance, $invoices);

        $payments = $this
            ->paymentRepository
            ->findByCustomerAndDateRange($balance->getCustomer(), $balance->getFrom(), $balance->getTo(), true);

        $this->buildPayments($balance, $payments);

        $this->setDoneLines($balance);

        $balance->sortLines();
    }

    /**
     * Adds the order balance.
     *
     * @param int   $orderId
     * @param float $balance
     */
    private function addOrderBalance(int $orderId, float $balance): void
    {
        if (isset($this->orders[$orderId])) {
            $this->orders[$orderId] += $balance;

            return;
        }

        $this->orders[$orderId] = $balance;
    }

    /**
     * Builds the invoices lines.
     *
     * @param Balance $balance
     * @param array   $invoices
     */
    private function buildInvoices(Balance $balance, array $invoices): void
    {
        foreach ($invoices as $data) {
            $debit = $credit = 0;
            $dueDate = null;

            if ($data['type'] === InvoiceTypes::TYPE_CREDIT) {
                $type = Line::TYPE_CREDIT;
                $credit = $data['grandTotal'];
            } else {
                $type = Line::TYPE_INVOICE;
                $debit = $data['grandTotal'];
                if ($data['limitDate']) {
                    $dueDate = new \DateTime($data['limitDate']);
                }
            }

            $line = new Line(
                new \DateTime($data['createdAt']),
                $type,
                $data['number'],
                $debit,
                $credit,
                $data['orderId'],
                $data['orderNumber'],
                new \DateTime($data['orderDate']),
                $dueDate
            );

            $balance->addLine($line);

            $this->addOrderBalance($data['orderId'], $credit - $debit);

            // TODO Remove whe payment refund (type) will be implemented
            if ($data['type'] === InvoiceTypes::TYPE_CREDIT && $data['factoryName'] === CreditBalance::FACTORY_NAME) {
                $line = new Line(
                    new \DateTime($data['createdAt']),
                    Line::TYPE_REFUND,
                    $data['number'],
                    $data['grandTotal'],
                    0,
                    $data['orderId'],
                    $data['orderNumber'],
                    new \DateTime($data['orderDate'])
                );

                $balance->addLine($line);

                $this->addOrderBalance($data['orderId'], $data['grandTotal']);
            }
        }
    }

    /**
     * Builds the payments lines.
     *
     * @param Balance $balance
     * @param array   $payments
     */
    private function buildPayments(Balance $balance, array $payments): void
    {
        foreach ($payments as $data) {
            $debit = $credit = 0;

            if ($data['state'] === PaymentStates::STATE_REFUNDED) {
                $type = Line::TYPE_REFUND;
                $debit = $data['amount'];
            } else {
                $type = Line::TYPE_PAYMENT;
                $credit = $data['amount'];
            }

            $line = new Line(
                new \DateTime($data['completedAt']),
                $type,
                $data['number'],
                $debit,
                $credit,
                $data['orderId'],
                $data['orderNumber'],
                new \DateTime($data['orderDate'])
            );

            $balance->addLine($line);

            $this->addOrderBalance($data['orderId'], $credit - $debit);
        }
    }

    /**
     * Marks lines as done if they are.
     *
     * @param Balance $balance
     */
    private function setDoneLines(Balance $balance): void
    {
        foreach ($balance->getLines() as $line) {
            if (!isset($this->orders[$line->getOrderId()])) {
                continue;
            }

            if (0 === bccomp($this->orders[$line->getOrderId()], 0, 2)) {
                $line->setDone(true);
            }
        }
    }
}
