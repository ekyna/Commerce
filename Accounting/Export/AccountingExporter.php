<?php

namespace Ekyna\Component\Commerce\Accounting\Export;

use Ekyna\Component\Commerce\Accounting\Model\AccountingInterface;
use Ekyna\Component\Commerce\Accounting\Model\AccountingTypes;
use Ekyna\Component\Commerce\Accounting\Repository\AccountingRepositoryInterface;
use Ekyna\Component\Commerce\Common\Model\AdjustmentModes;
use Ekyna\Component\Commerce\Common\Model\AdjustmentTypes;
use Ekyna\Component\Commerce\Common\Util\Money;
use Ekyna\Component\Commerce\Customer\Model\CustomerGroupInterface;
use Ekyna\Component\Commerce\Document\Model\DocumentLineTypes;
use Ekyna\Component\Commerce\Exception\LogicException;
use Ekyna\Component\Commerce\Exception\RuntimeException;
use Ekyna\Component\Commerce\Invoice\Model\InvoiceInterface;
use Ekyna\Component\Commerce\Invoice\Model\InvoiceTypes;
use Ekyna\Component\Commerce\Invoice\Repository\InvoiceRepositoryInterface;
use Ekyna\Component\Commerce\Invoice\Resolver\InvoicePaymentResolverInterface;
use Ekyna\Component\Commerce\Payment\Model\PaymentMethodInterface;
use Ekyna\Component\Commerce\Payment\Model\PaymentStates;
use Ekyna\Component\Commerce\Payment\Repository\PaymentRepositoryInterface;
use Ekyna\Component\Commerce\Pricing\Model\TaxRuleInterface;
use Ekyna\Component\Commerce\Pricing\Resolver\TaxResolverInterface;

/**
 * Class AccountingExporter
 * @package Ekyna\Component\Commerce\Accounting\Export
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class AccountingExporter implements AccountingExporterInterface
{
    /**
     * @var InvoiceRepositoryInterface
     */
    protected $invoiceRepository;

    /**
     * @var PaymentRepositoryInterface
     */
    protected $paymentRepository;

    /**
     * @var AccountingRepositoryInterface
     */
    protected $accountingRepository;

    /**
     * @var InvoicePaymentResolverInterface
     */
    protected $invoicePaymentResolver;

    /**
     * @var TaxResolverInterface
     */
    protected $taxResolver;

    /**
     * @var array
     */
    protected $config;

    /**
     * @var AccountingInterface[]
     */
    protected $accounts;

    /**
     * @var AccountingWriter
     */
    protected $writer;

    /**
     * @var InvoiceInterface
     */
    protected $invoice;

    /**
     * @var string
     */
    protected $currency;

    /**
     * @var float
     */
    protected $balance;


    /**
     * Constructor.
     *
     * @param InvoiceRepositoryInterface      $invoiceRepository
     * @param PaymentRepositoryInterface      $paymentRepository
     * @param AccountingRepositoryInterface   $accountingRepository
     * @param InvoicePaymentResolverInterface $invoicePaymentResolver
     * @param TaxResolverInterface            $taxResolver
     * @param array                           $config
     */
    public function __construct(
        InvoiceRepositoryInterface $invoiceRepository,
        PaymentRepositoryInterface $paymentRepository,
        AccountingRepositoryInterface $accountingRepository,
        InvoicePaymentResolverInterface $invoicePaymentResolver,
        TaxResolverInterface $taxResolver,
        array $config
    ) {
        $this->invoiceRepository = $invoiceRepository;
        $this->paymentRepository = $paymentRepository;
        $this->accountingRepository = $accountingRepository;
        $this->invoicePaymentResolver = $invoicePaymentResolver;
        $this->taxResolver = $taxResolver;

        $this->config = array_replace([
            'default_customer' => '10000000',
            'total_as_payment' => false,
        ], $config);
    }

    /**
     * @inheritDoc
     */
    public function export(\DateTime $month)
    {
        $path = tempnam(sys_get_temp_dir(), 'acc');

        $zip = new \ZipArchive();

        if (false === $zip->open($path)) {
            throw new RuntimeException("Failed to open '$path' for writing.");
        }

        $zip->addFile($this->exportInvoices($month), 'invoices.csv');

        if (!$this->config['total_as_payment']) {
            $zip->addFile($this->exportPayments($month), 'payments.csv');
        }

        $zip->close();

        return $path;
    }

    /**
     * @inheritDoc
     */
    public function exportInvoices(\DateTime $month)
    {
        $this->accounts = $this->accountingRepository->findBy([
            'enabled' => true,
        ]);
        if (empty($this->accounts)) {
            throw new LogicException("No account number configured.");
        }

        $path = tempnam(sys_get_temp_dir(), 'inv');

        $this->writer = new AccountingWriter();
        $this->writer->open($path);

        $WRONG_BALANCES = [];

        $invoices = $this->invoiceRepository->findByMonth($month);

        while (false !== $this->invoice = current($invoices)) {
            $this->currency = $this->invoice->getCurrency();
            $this->balance = 0;

            $this->writer->configure($this->invoice);

            $this->writeInvoiceGrandTotal();
            $this->writeInvoiceGoodsLines();
            $this->writeInvoiceShipmentLine();
            $this->writeInvoiceTaxesLine();

            if (0 !== $this->compare($this->balance, 0)) {
                $WRONG_BALANCES[] = $this->invoice->getNumber();
            }

            next($invoices);
        }

        $this->writer->close();

        return $path;
    }

    /**
     * @inheritDoc
     */
    public function exportPayments(\DateTime $month)
    {
        $path = tempnam(sys_get_temp_dir(), 'acc');

        $this->writer = new AccountingWriter();
        $this->writer->open($path);

        $payments = $this->paymentRepository->findByMonth($month, [
            PaymentStates::STATE_CAPTURED,
            PaymentStates::STATE_COMPLETED,
            PaymentStates::STATE_REFUNDED,
        ]);

        foreach ($payments as $payment) {
            $this->writer->configure($payment);

            $account = $this->getPaymentAccountNumber($payment->getMethod());

            if ($customer = $payment->getSale()->getCustomer()) {
                $number = '1' . str_pad($customer->getId(), '7', '0', STR_PAD_LEFT);
            } else {
                $number = '10000000';
            }

            $credit = false; // TODO $payment->getType() === PaymentTypes::TYPE_REFUND;
            $amount = (string)$this->round($payment->getAmount());

            if ($credit) {
                // Payment debit
                $this->writer->debit($account, $amount);
                // Customer credit
                $this->writer->credit($number, $amount);
            } else {
                // Payment credit
                $this->writer->credit($account, $amount);
                // Customer debit
                $this->writer->debit($number, $amount);
            }

            // TODO Remove when refund payment implemented
            // Temporary : add an extra credit line for refund payments.
            if ($payment->getState() === PaymentStates::STATE_REFUNDED) {
                // Payment debit
                $this->writer->debit($account, $amount);
                // Customer credit
                $this->writer->credit($number, $amount);
            }
        }

        $this->writer->close();

        return $path;
    }

    /**
     * Writes the invoice's grand total line(s).
     */
    protected function writeInvoiceGrandTotal()
    {
        $sale = $this->invoice->getSale();

        // Grand total row
        if ($this->config['total_as_payment']) {

            // Credit case
            if ($this->invoice->getType() === InvoiceTypes::TYPE_CREDIT) {
                $account = $this->getPaymentAccountNumber($this->invoice->getPaymentMethod());

                $amount = $this->round($this->invoice->getGrandTotal());

                $this->writer->debit($account, (string)$amount);
                $this->balance += $amount;

                return;
            }

            // Invoice case
            $unpaid = $this->invoice->getGrandTotal();

            $payments = $this->invoicePaymentResolver->resolve($this->invoice);

            // Payments
            foreach ($payments as $payment) {
                $account = $this->getPaymentAccountNumber($payment->getPayment()->getMethod());

                $amount = $this->round($payment->getAmount());

                $this->writer->credit($account, (string)$amount);

                $unpaid -= $amount;
                $this->balance -= $amount;
            }

            // Unpaid amount
            if (1 === $this->compare($unpaid, 0)) {
                $account = $this->getUnpaidAccountNumber($sale->getCustomerGroup());

                $this->writer->credit($account, (string)$unpaid);

                $this->balance -= $unpaid;
            }

            return;
        }

        if ($customer = $sale->getCustomer()) {
            $account = '1' . str_pad($customer->getId(), '7', '0', STR_PAD_LEFT);
        } else {
            $account = $this->config['default_customer'];
        }

        $amount = $this->round($this->invoice->getGrandTotal());

        if ($this->invoice->getType() === InvoiceTypes::TYPE_CREDIT) {
            $this->writer->debit($account, (string)$amount);
            $this->balance += $amount;
        } else {
            $this->writer->credit($account, (string)$amount);
            $this->balance -= $amount;
        }
    }

    /**
     * Writes the invoice's goods line.
     */
    protected function writeInvoiceGoodsLines()
    {
        $sale = $this->invoice->getSale();
        $taxRule = $this->taxResolver->resolveSaleTaxRule($sale);
        /** @var \Ekyna\Component\Commerce\Common\Model\AdjustmentInterface[] $discounts */
        $discounts = $sale->getAdjustments(AdjustmentTypes::TYPE_DISCOUNT)->toArray();

        // Gather amounts by tax rates
        $amounts = [];
        foreach ($this->invoice->getLinesByType(DocumentLineTypes::TYPE_GOOD) as $line) {
            // Skip private lines
            if ($line->getSaleItem()->isPrivate()) {
                continue;
            }

            $rates = $line->getTaxRates();
            if (empty($rates)) {
                $rate = 0;
            } elseif (1 === count($rates)) {
                $rate = current($rates);
            } else {
                throw new LogicException("Multiple tax rates on goods lines are not yet supported."); // TODO
            }

            $amount = $line->getBase();

            // Apply sale's discounts
            if (!empty($discounts)) {
                $base = $amount;
                foreach ($discounts as $adjustment) {
                    if ($adjustment->getMode() === AdjustmentModes::MODE_PERCENT) {
                        $amount -= $this->round($amount * $adjustment->getAmount() / 100);
                    } else {
                        $amount -= $this->round($base / $this->invoice->getGoodsBase() * $adjustment->getAmount());
                    }
                }
            }

            if (!isset($amounts[(string)$rate])) {
                $amounts[(string)$rate] = 0;
            }

            $amounts[(string)$rate] += $this->round($amount);
        }

        $credit = $this->invoice->getType() === InvoiceTypes::TYPE_CREDIT;

        // Writes each tax rates's amount
        foreach ($amounts as $rate => $amount) {
            $amount = $this->round($amount);

            if (0 === $this->compare($amount, 0)) {
                continue; // next tax rate
            }

            $account = $this->getGoodAccountNumber($taxRule, (float)$rate);

            if ($credit) {
                $this->writer->credit($account, (string)$amount);
                $this->balance -= $amount;
            } else {
                $this->writer->debit($account, (string)$amount);
                $this->balance += $amount;
            }
        }
    }

    /**
     * Writes the invoice's shipment line.
     */
    protected function writeInvoiceShipmentLine()
    {
        $amount = $this->invoice->getShipmentBase();

        if (0 === $this->compare($amount, 0)) {
            return;
        }

        $amount = $this->round($amount);

        $sale = $this->invoice->getSale();
        $taxRule = $this->taxResolver->resolveSaleTaxRule($sale);

        $account = $this->getShipmentAccountNumber($taxRule);

        if ($this->invoice->getType() === InvoiceTypes::TYPE_CREDIT) {
            $this->writer->credit($account, (string)$amount);
            $this->balance -= $amount;
        } else {
            $this->writer->debit($account, (string)$amount);
            $this->balance += $amount;
        }
    }

    /**
     * Writes the invoice's taxes lines.
     */
    protected function writeInvoiceTaxesLine()
    {
        $credit = $this->invoice->getType() === InvoiceTypes::TYPE_CREDIT;

        foreach ($this->invoice->getTaxesDetails() as $detail) {
            $amount = $this->round($detail['amount']);

            if (0 === $this->compare($amount, 0)) {
                continue; // next tax details
            }

            $account = $this->getTaxAccountNumber($detail['rate']);

            if ($credit) {
                $this->writer->credit($account, (string)$amount);
                $this->balance -= $amount;
            } else {
                $this->writer->debit($account, (string)$amount);
                $this->balance += $amount;
            }
        }
    }

    /**
     * Rounds the amount.
     *
     * @param $amount
     *
     * @return float
     */
    protected function round(float $amount)
    {
        // TODO currency conversion ?
        return Money::round($amount, $this->currency);
    }

    /**
     * Compare the amounts.
     *
     * @param $a
     * @param $b
     *
     * @return float
     */
    protected function compare(float $a, float $b)
    {
        // TODO currency conversion ?
        return Money::compare($a, $b, $this->currency);
    }

    /**
     * Return the goods account number for the given tax rule and tax rate.
     *
     * @param TaxRuleInterface $rule
     * @param float            $rate
     *
     * @return string
     */
    protected function getGoodAccountNumber(TaxRuleInterface $rule, float $rate)
    {
        foreach ($this->accounts as $account) {
            if ($account->getType() !== AccountingTypes::TYPE_GOOD) {
                continue;
            }

            if ($account->getTaxRule() !== $rule) {
                continue;
            }

            if (is_null($account->getTax())) {
                if ($rate == 0) {
                    return $account->getNumber();
                }

                continue;
            }

            if (0 === bccomp($account->getTax()->getRate(), $rate, 5)) {
                return $account->getNumber();
            }
        }

        throw new LogicException(sprintf(
            "No goods account number configured for tax rule '%s' and tax rate %s.",
            $rule->getName(),
            $rate
        ));
    }

    /**
     * Returns the shipment account number for the given tax rule.
     *
     * @param TaxRuleInterface $rule
     *
     * @return string
     */
    protected function getShipmentAccountNumber(TaxRuleInterface $rule)
    {
        foreach ($this->accounts as $account) {
            if ($account->getType() !== AccountingTypes::TYPE_SHIPPING) {
                continue;
            }

            if ($account->getTaxRule() !== $rule) {
                continue;
            }

            return $account->getNumber();
        }

        throw new LogicException(sprintf(
            "No shipment account number configured for tax rule '%s'.",
            $rule->getName()
        ));
    }

    /**
     * Returns the tax account number for the given tax rate.
     *
     * @param float $rate
     *
     * @return string
     */
    protected function getTaxAccountNumber(float $rate)
    {
        foreach ($this->accounts as $account) {
            if ($account->getType() !== AccountingTypes::TYPE_TAX) {
                continue;
            }

            if (0 !== bccomp($account->getTax()->getRate(), $rate, 5)) {
                continue;
            }

            return $account->getNumber();
        }

        throw new LogicException(sprintf(
            "No tax account number configured for tax rate '%s'.",
            $rate
        ));
    }

    /**
     * Returns the payment account number for the given payment method.
     *
     * @param PaymentMethodInterface $method
     *
     * @return string
     */
    protected function getPaymentAccountNumber(PaymentMethodInterface $method)
    {
        foreach ($this->accounts as $account) {
            if ($account->getType() !== AccountingTypes::TYPE_PAYMENT) {
                continue;
            }

            if ($account->getPaymentMethod() !== $method) {
                continue;
            }

            return $account->getNumber();
        }

        throw new LogicException(sprintf(
            "No payment account number configured for payment method '%s'.",
            $method->getName()
        ));
    }

    /**
     * Returns the unpaid account number for the given customer group.
     *
     * @param CustomerGroupInterface $group
     *
     * @return string
     */
    protected function getUnpaidAccountNumber(CustomerGroupInterface $group)
    {
        foreach ($this->accounts as $account) {
            if ($account->getType() !== AccountingTypes::TYPE_UNPAID) {
                continue;
            }

            foreach ($account->getCustomerGroups() as $g) {
                if ($g->getId() === $group->getId()) {
                    return $account->getNumber();
                }
            }
        }

        // Fallback to 'all' (empty) customer groups
        foreach ($this->accounts as $account) {
            if ($account->getType() !== AccountingTypes::TYPE_UNPAID) {
                continue;
            }

            if (0 < $account->getCustomerGroups()->count()) {
                continue;
            }

            return $account->getNumber();
        }

        throw new LogicException(sprintf(
            "No unpaid account number configured for customer group '%s'.",
            $group->getName()
        ));
    }
}
