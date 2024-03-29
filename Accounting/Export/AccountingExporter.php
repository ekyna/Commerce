<?php

namespace Ekyna\Component\Commerce\Accounting\Export;

use Ekyna\Component\Commerce\Accounting\Model\AccountingInterface;
use Ekyna\Component\Commerce\Accounting\Model\AccountingTypes;
use Ekyna\Component\Commerce\Accounting\Repository\AccountingRepositoryInterface;
use Ekyna\Component\Commerce\Common\Calculator\AmountCalculatorFactory;
use Ekyna\Component\Commerce\Common\Currency\CurrencyConverterInterface;
use Ekyna\Component\Commerce\Common\Model\AdjustmentModes;
use Ekyna\Component\Commerce\Common\Util\Money;
use Ekyna\Component\Commerce\Customer\Model\CustomerGroupInterface;
use Ekyna\Component\Commerce\Customer\Model\CustomerInterface;
use Ekyna\Component\Commerce\Document\Calculator\DocumentCalculatorInterface;
use Ekyna\Component\Commerce\Document\Model\DocumentLineTypes;
use Ekyna\Component\Commerce\Document\Util\DocumentUtil;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Exception\LogicException;
use Ekyna\Component\Commerce\Exception\RuntimeException;
use Ekyna\Component\Commerce\Invoice\Model\InvoiceInterface;
use Ekyna\Component\Commerce\Invoice\Repository\InvoiceRepositoryInterface;
use Ekyna\Component\Commerce\Invoice\Resolver\InvoicePaymentResolverInterface;
use Ekyna\Component\Commerce\Payment\Model\PaymentInterface;
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
     * @var CurrencyConverterInterface
     */
    protected $currencyConverter;

    /**
     * @var AmountCalculatorFactory
     */
    protected $calculatorFactory;

    /**
     * @var DocumentCalculatorInterface
     */
    protected $invoiceCalculator;

    /**
     * @var InvoicePaymentResolverInterface
     */
    protected $invoicePaymentResolver;

    /**
     * @var TaxResolverInterface
     */
    protected $taxResolver;

    /**
     * @var AccountingFilterInterface[]
     */
    protected $filters;

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
     * @param CurrencyConverterInterface      $currencyConverter
     * @param AmountCalculatorFactory         $calculatorFactory
     * @param DocumentCalculatorInterface     $invoiceCalculator
     * @param InvoicePaymentResolverInterface $invoicePaymentResolver
     * @param TaxResolverInterface            $taxResolver
     * @param array                           $config
     */
    public function __construct(
        InvoiceRepositoryInterface $invoiceRepository,
        PaymentRepositoryInterface $paymentRepository,
        AccountingRepositoryInterface $accountingRepository,
        CurrencyConverterInterface $currencyConverter,
        AmountCalculatorFactory $calculatorFactory,
        DocumentCalculatorInterface $invoiceCalculator,
        InvoicePaymentResolverInterface $invoicePaymentResolver,
        TaxResolverInterface $taxResolver,
        array $config
    ) {
        $this->invoiceRepository = $invoiceRepository;
        $this->paymentRepository = $paymentRepository;
        $this->accountingRepository = $accountingRepository;
        $this->currencyConverter = $currencyConverter;
        $this->calculatorFactory = $calculatorFactory;
        $this->invoiceCalculator = $invoiceCalculator;
        $this->invoicePaymentResolver = $invoicePaymentResolver;
        $this->taxResolver = $taxResolver;

        $this->filters = [];
        $this->currency = $this->currencyConverter->getDefaultCurrency();

        $this->config = array_replace([
            'default_customer' => '10000000',
            'total_as_payment' => false,
        ], $config);
    }

    /**
     * @inheritDoc
     */
    public function addFilter(AccountingFilterInterface $filter): void
    {
        if (in_array($filter, $this->filters, true)) {
            throw new LogicException("Filter is already registered.");
        }

        $this->filters[] = $filter;
    }

    /**
     * @inheritDoc
     */
    public function export(string $year, string $month = null): string
    {
        ini_set('max_execution_time', 0);

        $months = [];
        if (is_null($month)) {
            try {
                $start = new \DateTime("$year-01-01");
            } catch (\Exception $e) {
                throw new InvalidArgumentException("Failed to create date.");
            }
            $months = iterator_to_array(new \DatePeriod(
                $start,
                new \DateInterval('P1M'),
                (clone $start)->modify('last day of december')
            ));
        } else {
            try {
                $months[] = new \DateTime("$year-$month-01");
            } catch (\Exception $e) {
                throw new InvalidArgumentException("Failed to create date.");
            }
        }

        $path = tempnam(sys_get_temp_dir(), 'accounting');

        $zip = new \ZipArchive();

        if (false === $zip->open($path)) {
            throw new RuntimeException("Failed to open '$path' for writing.");
        }

        /** @var \DateTime $month */
        foreach ($months as $month) {
            $zip->addFile($this->exportInvoices($month), sprintf('%s_invoices.csv', $month->format('Y-m')));

            if (!$this->config['total_as_payment']) {
                $zip->addFile($this->exportPayments($month), sprintf('%s_payments.csv', $month->format('Y-m')));
            }
        }

        $zip->close();

        return $path;
    }

    /**
     * @inheritDoc
     */
    public function exportInvoices(\DateTime $month): string
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

        $invoices = $this->invoiceRepository->findByMonth($month);

        while (false !== $this->invoice = current($invoices)) {
            if (!$this->filterInvoice($this->invoice)) {
                continue;
            }

            if ($this->invoice->getCurrency() !== $this->currency) {
                $this->invoice = clone $this->invoice;
                $this->invoice->setCurrency($this->currency);

                $this->invoiceCalculator->calculate($this->invoice);
            }

            $this->balance = 0;

            $this->writer->configure($this->invoice);

            $this->writeInvoiceGrandTotal();
            $this->writeInvoiceGoodsLines();
            $this->writeInvoiceShipmentLine();
            $this->writeInvoiceTaxesLine();
            $this->writeInvoiceItemsLines();

            next($invoices);
        }

        $this->writer->close();

        return $path;
    }

    /**
     * @inheritDoc
     */
    public function exportPayments(\DateTime $month): string
    {
        $path = tempnam(sys_get_temp_dir(), 'acc');

        $this->writer = new AccountingWriter();
        $this->writer->open($path);

        $payments = $this->paymentRepository->findByMonth($month, PaymentStates::getCompletedStates(true));

        foreach ($payments as $payment) {
            if (!$this->filterPayment($payment)) {
                continue;
            }

            $method = $payment->getMethod();
            if ($method->isOutstanding()) {
                continue;
            }

            $this->writer->configure($payment);

            $sale = $payment->getSale();

            $account = $this->getPaymentAccountNumber($method, $payment->getNumber());
            $number = $this->getCustomerAccountNumber($sale->getCustomer());

            $amount = (string)$this->round($payment->getRealAmount());
            $date = $sale->getCreatedAt();

            if ($payment->isRefund()) {
                // Payment debit
                $this->writer->debit($account, $amount, $date);
                // Customer credit
                $this->writer->credit($number, $amount, $date);
            } else {
                // Payment credit
                $this->writer->credit($account, $amount, $date);
                // Customer debit
                $this->writer->debit($number, $amount, $date);

                // Add an extra credit line for refund payments.
                if ($payment->getState() === PaymentStates::STATE_REFUNDED) {
                    // Payment debit
                    $this->writer->debit($account, $amount, $date);
                    // Customer credit
                    $this->writer->credit($number, $amount, $date);
                }
            }

            $this->writePaymentExchange($payment, $number, $date);
        }

        $this->writer->close();

        return $path;
    }

    /**
     * Writes the invoice's grand total line(s).
     */
    protected function writeInvoiceGrandTotal(): void
    {
        $sale = $this->invoice->getSale();
        $date = $sale->getCreatedAt();

        // Grand total row
        if ($this->config['total_as_payment']) {
            // Invoice case
            $unpaid = $this->invoice->getGrandTotal();

            $invoicePayments = $this->invoicePaymentResolver->resolve($this->invoice);

            // Payments
            foreach ($invoicePayments as $invoicePayment) {
                if (!$payment = $invoicePayment->getPayment()) {
                    continue;
                }

                $account = $this->getPaymentAccountNumber($payment->getMethod(), $payment->getNumber());

                $amount = $this->round($invoicePayment->getAmount());
                $unpaid -= $amount;

                if ($invoicePayment->getPayment()->isRefund()) {
                    $this->writer->debit($account, (string)$amount, $date);
                    $this->balance += $amount;
                } else {
                    $this->writer->credit($account, (string)$amount, $date);
                    $this->balance -= $amount;
                }
            }

            // Unpaid amount
            if (1 === $this->compare($unpaid, 0)) {
                $account = $this->getUnpaidAccountNumber($sale->getCustomerGroup(), $this->invoice->getNumber());

                if ($this->invoice->isCredit()) {
                    $this->writer->debit($account, (string)$unpaid, $date);
                    $this->balance += $unpaid;
                } else {
                    $this->writer->credit($account, (string)$unpaid, $date);
                    $this->balance -= $unpaid;
                }
            }

            return;
        }

        $account = $this->getCustomerAccountNumber($sale->getCustomer());
        $amount = $this->round($this->invoice->getGrandTotal());

        if ($this->invoice->isCredit()) {
            $this->writer->debit($account, (string)$amount, $date);
            $this->balance += $amount;
        } else {
            $this->writer->credit($account, (string)$amount, $date);
            $this->balance -= $amount;
        }
    }

    /**
     * Writes the invoice's goods lines.
     */
    protected function writeInvoiceGoodsLines(): void
    {
        $sale = $this->invoice->getSale();
        $date = $sale->getCreatedAt();
        $taxRule = $this->invoice->getTaxRule() ?: $this->taxResolver->resolveSaleTaxRule($sale);
        $discounts = $this->getSaleDiscounts();

        // Gather amounts by tax rates
        $amounts = [];
        foreach ($this->invoice->getLinesByType(DocumentLineTypes::TYPE_GOOD) as $line) {
            $item = $line->getSaleItem();
            // Skip private lines if document has one of its public parent.
            if ($item->isPrivate() && DocumentUtil::hasPublicParent($line->getDocument(), $item)) {
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

            if (!isset($amounts[(string)$rate])) {
                $amounts[(string)$rate] = 0;
            }

            $amounts[(string)$rate] += $this->round($amount);
        }

        // Writes each tax rates's amount
        foreach ($amounts as $rate => $amount) {
            $amount = $this->round($amount);

            // Apply sale's discounts
            if (!empty($discounts)) {
                $base = $amount;
                foreach ($discounts as $adjustment) {
                    if ($adjustment->getMode() === AdjustmentModes::MODE_PERCENT) {
                        $amount -= $this->round($amount * $adjustment->getAmount() / 100);
                    } else {
                        $gross = $this->calculatorFactory->create($this->currency)->calculateSale($sale, true);
                        $amount -= $this->round($base * $adjustment->getAmount() / $gross->getBase());
                    }
                }
            }

            if (0 === $this->compare($amount, 0)) {
                continue; // next tax rate
            }

            $account = $this->getGoodAccountNumber($taxRule, (float)$rate, $this->invoice->getNumber());

            if ($this->invoice->isCredit()) {
                $this->writer->credit($account, (string)$amount, $date);
                $this->balance -= $amount;
            } else {
                $this->writer->debit($account, (string)$amount, $date);
                $this->balance += $amount;
            }
        }
    }

    /**
     * Writes the invoice's goods items.
     */
    protected function writeInvoiceItemsLines(): void
    {
        $sale = $this->invoice->getSale();
        $date = $sale->getCreatedAt();
        $taxRule = $this->invoice->getTaxRule() ?: $this->taxResolver->resolveSaleTaxRule($sale);
        $discounts = $this->getSaleDiscounts();

        // Gather amounts by tax rates
        $amounts = [];
        foreach ($this->invoice->getItems() as $item) {
            $rates = $item->getTaxRates();
            if (empty($rates)) {
                $rate = 0;
            } elseif (1 === count($rates)) {
                $rate = current($rates);
            } else {
                throw new LogicException("Multiple tax rates on goods lines are not yet supported."); // TODO
            }

            $amount = $item->getBase();

            if (!isset($amounts[(string)$rate])) {
                $amounts[(string)$rate] = 0;
            }

            $amounts[(string)$rate] += $this->round($amount);
        }

        // Writes each tax rates's amount
        foreach ($amounts as $rate => $amount) {
            $amount = $this->round($amount);

            // Apply sale's discounts
            if (!empty($discounts)) {
                $base = $amount;
                foreach ($discounts as $adjustment) {
                    if ($adjustment->getMode() === AdjustmentModes::MODE_PERCENT) {
                        $amount -= $this->round($amount * $adjustment->getAmount() / 100);
                    } else {
                        $gross = $this->calculatorFactory->create($this->currency)->calculateSale($sale, true);
                        $amount -= $this->round($base * $adjustment->getAmount() / $gross->getBase());
                    }
                }
            }

            if (0 === $this->compare($amount, 0)) {
                continue; // next tax rate
            }

            $account = $this->getAdjustmentAccountNumber($taxRule, (float)$rate, $this->invoice->getNumber());

            if ($this->invoice->isCredit()) {
                $this->writer->credit($account, (string)$amount, $date);
                $this->balance -= $amount;
            } else {
                $this->writer->debit($account, (string)$amount, $date);
                $this->balance += $amount;
            }
        }
    }

    /**
     * Writes the invoice's shipment line.
     */
    protected function writeInvoiceShipmentLine(): void
    {
        $amount = $this->invoice->getShipmentBase();

        if (0 === $this->compare($amount, 0)) {
            return;
        }

        $amount = $this->round($amount);

        $sale = $this->invoice->getSale();
        $date = $sale->getCreatedAt();
        $taxRule = $this->invoice->getTaxRule() ?: $this->taxResolver->resolveSaleTaxRule($sale);

        $account = $this->getShipmentAccountNumber($taxRule, $this->invoice->getNumber());

        if ($this->invoice->isCredit()) {
            $this->writer->credit($account, (string)$amount, $date);
            $this->balance -= $amount;
        } else {
            $this->writer->debit($account, (string)$amount, $date);
            $this->balance += $amount;
        }
    }

    /**
     * Writes the invoice's taxes lines.
     */
    protected function writeInvoiceTaxesLine(): void
    {
        $sale = $this->invoice->getSale();
        $date = $sale->getCreatedAt();

        foreach ($this->invoice->getTaxesDetails() as $detail) {
            $amount = $this->round($detail['amount']);

            if (0 === $this->compare($amount, 0)) {
                continue; // next tax details
            }

            $account = $this->getTaxAccountNumber($detail['rate'], $this->invoice->getNumber());

            if ($this->invoice->isCredit()) {
                $this->writer->credit($account, (string)$amount, $date);
                $this->balance -= $amount;
            } else {
                $this->writer->debit($account, (string)$amount, $date);
                $this->balance += $amount;
            }
        }
    }

    /**
     * Writes the payment exchange loss or gain.
     *
     * @param PaymentInterface $payment
     * @param string           $number
     * @param \DateTime        $date
     */
    protected function writePaymentExchange(PaymentInterface $payment, string $number, \DateTime $date): void
    {
        $sale = $payment->getSale();
        $pc = $payment->getCurrency()->getCode();
        $sc = $sale->getCurrency()->getCode();

        if ($this->currency !== $pc) { // If payment currency is not default
            $currency = $pc;
            $rate = $this->currencyConverter->getSubjectExchangeRate($payment, $currency, $this->currency);

            $amount = $payment->getAmount();

            $grandTotal = $this->calculatorFactory->create($currency)->calculateSale($sale)->getTotal();

            if ($this->currency === $sc) {  // If sale currency is default
                $amount = $this->currencyConverter->convertWithRate($amount, $rate, $this->currency);
            }
        } elseif ($this->currency !== $sc) {  // If sale currency is not default
            $currency = $sc;
            $rate = $this->currencyConverter->getSubjectExchangeRate($sale, $currency, $this->currency);

            $amount = $payment->getAmount();

            $grandTotal = $this->calculatorFactory->create($currency)->calculateSale($sale)->getTotal();

            if ($this->currency === $pc) {  // If payment currency is default
                $grandTotal = $this->currencyConverter->convertWithRate($grandTotal, $rate, $this->currency);
            }
        } else {
            return;
        }

        $realGrandTotal = $this->calculatorFactory->create($this->currency)->calculateSale($sale)->getTotal();

        // Diff = Payment amount DC - (Sale total DC * Payment amount FC / Sale total FC)
        // (DC: default currency, FC: foreign currency)
        $diff = $payment->getRealAmount() - ($realGrandTotal * $amount / $grandTotal);

        if (0 === $c = $this->compare($diff, 0)) {
            return;
        }

        if (1 === $c) {
            // Gain
            $account = $this->getExchangeAccountNumber(true);
        } else {
            // Loss
            $account = $this->getExchangeAccountNumber(false);
            $diff = -$diff;
        }

        if (null === $account) {
            return;
        }

        $diff = (string)$this->round($diff);

        if ($payment->isRefund()) {
            // Payment debit
            $this->writer->credit($account, $diff, $date);
            // Customer credit
            $this->writer->debit($number, $diff, $date);
        } else {
            // Payment credit
            $this->writer->debit($account, $diff, $date);
            // Customer debit
            $this->writer->credit($number, $diff, $date);

            if ($payment->getState() === PaymentStates::STATE_REFUNDED) {
                // Payment debit
                $this->writer->credit($account, $diff, $date);
                // Customer credit
                $this->writer->debit($number, $diff, $date);
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
    protected function round(float $amount): float
    {
        return Money::round($amount, $this->currency);
    }

    /**
     * Compare the amounts.
     *
     * @param $a
     * @param $b
     *
     * @return int
     */
    protected function compare(float $a, float $b): int
    {
        return Money::compare($a, $b, $this->currency);
    }

    /**
     * Returns the sale discounts included in the current invoice.
     *
     * @return \Ekyna\Component\Commerce\Common\Model\AdjustmentInterface[]
     */
    protected function getSaleDiscounts(): array
    {
        $adjustments = [];
        foreach ($this->invoice->getLinesByType(DocumentLineTypes::TYPE_DISCOUNT) as $line) {
            $adjustments[] = $line->getSaleAdjustment();
        }

        return $adjustments;
    }

    /**
     * Returns the customer account number.
     *
     * @param CustomerInterface|null $customer
     *
     * @return string
     */
    protected function getCustomerAccountNumber(CustomerInterface $customer = null): string
    {
        if ($customer) {
            return '1' . str_pad($customer->getId(), '7', '0', STR_PAD_LEFT);
        }

        return $this->config['default_customer'];
    }

    /**
     * Return the goods account number for the given tax rule and tax rate.
     *
     * @param TaxRuleInterface $rule
     * @param float            $rate
     * @param string           $origin
     *
     * @return string
     */
    protected function getGoodAccountNumber(TaxRuleInterface $rule, float $rate, string $origin): string
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
            "No goods account number configured for tax rule '%s' and tax rate %s (%s)",
            $rule->getName(),
            $rate,
            $origin
        ));
    }

    /**
     * Returns the shipment account number for the given tax rule.
     *
     * @param TaxRuleInterface $rule
     * @param string           $origin
     *
     * @return string
     */
    protected function getShipmentAccountNumber(TaxRuleInterface $rule, string $origin): string
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
            "No shipment account number configured for tax rule '%s' (%s)",
            $rule->getName(),
            $origin
        ));
    }

    /**
     * Returns the tax account number for the given tax rate.
     *
     * @param float  $rate
     * @param string $origin
     *
     * @return string
     */
    protected function getTaxAccountNumber(float $rate, string $origin): string
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
            "No tax account number configured for tax rate '%s' (%s)",
            $rate,
            $origin
        ));
    }

    /**
     * Returns the payment account number for the given payment method.
     *
     * @param PaymentMethodInterface $method
     * @param string                 $origin
     *
     * @return string
     */
    protected function getPaymentAccountNumber(PaymentMethodInterface $method, string $origin): string
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
            "No payment account number configured for payment method '%s' (%s)",
            $method->getName(),
            $origin
        ));
    }

    /**
     * Returns the unpaid account number for the given customer group.
     *
     * @param CustomerGroupInterface $group
     * @param string                 $origin
     *
     * @return string
     */
    protected function getUnpaidAccountNumber(CustomerGroupInterface $group, string $origin): string
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
            "No unpaid account number configured for customer group '%s' (%s)",
            $group->getName(),
            $origin
        ));
    }

    /**
     * Returns exchange gain or lose account number.
     *
     * @param bool $gain
     *
     * @return string|null
     */
    protected function getExchangeAccountNumber(bool $gain = true): ?string
    {
        foreach ($this->accounts as $account) {
            if ($gain && ($account->getType() === AccountingTypes::TYPE_EX_GAIN)) {
                return $account->getNumber();
            }
            if (!$gain && ($account->getType() === AccountingTypes::TYPE_EX_LOSS)) {
                return $account->getNumber();
            }
        }

        return null;
    }

    /**
     * Return the adjustment account number for the given tax rule and tax rate.
     *
     * @param TaxRuleInterface $rule
     * @param float            $rate
     * @param string           $origin
     *
     * @return string
     */
    protected function getAdjustmentAccountNumber(TaxRuleInterface $rule, float $rate, string $origin): string
    {
        foreach ($this->accounts as $account) {
            if ($account->getType() !== AccountingTypes::TYPE_ADJUSTMENT) {
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
            "No adjustment account number configured for tax rule '%s' and tax rate %s (%s)",
            $rule->getName(),
            $rate,
            $origin
        ));
    }

    /**
     * Filters the invoice.
     *
     * @param InvoiceInterface $invoice
     *
     * @return bool Whether the invoice should be exported.
     */
    private function filterInvoice(InvoiceInterface $invoice): bool
    {
        foreach ($this->filters as $filter) {
            if (!$filter->filterInvoice($invoice)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Filters the payment.
     *
     * @param PaymentInterface $payment
     *
     * @return bool Whether the payment should be exported.
     */
    private function filterPayment(PaymentInterface $payment): bool
    {
        foreach ($this->filters as $filter) {
            if (!$filter->filterPayment($payment)) {
                return false;
            }
        }

        return true;
    }
}
