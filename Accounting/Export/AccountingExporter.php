<?php

namespace Ekyna\Component\Commerce\Accounting\Export;

use Ekyna\Component\Commerce\Accounting\Model\AccountingTypes;
use Ekyna\Component\Commerce\Accounting\Repository\AccountingRepositoryInterface;
use Ekyna\Component\Commerce\Common\Model\AdjustmentTypes;
use Ekyna\Component\Commerce\Common\Util\Money;
use Ekyna\Component\Commerce\Document\Model\DocumentLineTypes;
use Ekyna\Component\Commerce\Exception\RuntimeException;
use Ekyna\Component\Commerce\Invoice\Model\InvoiceTypes;
use Ekyna\Component\Commerce\Invoice\Repository\InvoiceRepositoryInterface;
use Ekyna\Component\Commerce\Payment\Model\PaymentInterface;
use Ekyna\Component\Commerce\Payment\Model\PaymentStates;
use Ekyna\Component\Commerce\Payment\Repository\PaymentRepositoryInterface;
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
     * @var TaxResolverInterface
     */
    protected $taxResolver;

    /**
     * @var array
     */
    protected $accounts;

    /**
     * @var array
     */
    protected $config;


    /**
     * Constructor.
     *
     * @param InvoiceRepositoryInterface    $invoiceRepository
     * @param PaymentRepositoryInterface    $paymentRepository
     * @param AccountingRepositoryInterface $accountingRepository
     * @param TaxResolverInterface          $taxResolver
     * @param array                         $config
     */
    public function __construct(
        InvoiceRepositoryInterface $invoiceRepository,
        PaymentRepositoryInterface $paymentRepository,
        AccountingRepositoryInterface $accountingRepository,
        TaxResolverInterface $taxResolver,
        array $config
    ) {
        $this->invoiceRepository = $invoiceRepository;
        $this->paymentRepository = $paymentRepository;
        $this->accountingRepository = $accountingRepository;
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
        $zip->addFile($this->exportPayments($month), 'payments.csv');

        $zip->close();

        return $path;
    }

    /**
     * @inheritDoc
     */
    public function exportInvoices(\DateTime $month)
    {
        $path = tempnam(sys_get_temp_dir(), 'inv');

        if (false === $handle = fopen($path, "w")) {
            throw new RuntimeException("Failed to open '$path' for writing.");
        }

        $paymentAccounts = $this->accountingRepository->findByTypes([
            AccountingTypes::TYPE_PAYMENT,
        ]);

        $invoiceAccounts = $this->accountingRepository->findByTypes([
            AccountingTypes::TYPE_GOOD,
            AccountingTypes::TYPE_SHIPPING,
            AccountingTypes::TYPE_TAX,
        ]);

        $invoices = $this->invoiceRepository->findByMonth($month);

        foreach ($invoices as $invoice) {
            $date = $invoice->getCreatedAt()->format('Y-m-d'); // TODO localized ?
            $currency = $invoice->getCurrency();

            $sale = $invoice->getSale();
            $customer = $sale->getCustomer();

            if ($customer) {
                $identity = $customer->getFirstName() . ' ' . $customer->getLastName();
            } else {
                $identity = $sale->getFirstName() . ' ' . $sale->getLastName();
            }

            // Grand total row
            if ($this->config['total_as_payment']) {
                $payments = $sale->getPayments()->filter(function(PaymentInterface $payment) {
                    return PaymentStates::isPaidState($payment->getState());
                })->toArray();

                usort($payments, function(PaymentInterface $a, PaymentInterface $b) {
                    // TODO Currency conversion
                    if ($a->getAmount() == $b->getAmount()) {
                        return 0;
                    }

                    return $a->getAmount() > $b->getAmount() ? -1 : 1;
                });

                if (empty($payments)) {
                    continue; // Next invoice
                }

                /** @var PaymentInterface $payment */
                $payment = reset($payments);

                $found = false;
                /** @var \Ekyna\Component\Commerce\Accounting\Model\AccountingInterface $account */
                foreach ($paymentAccounts as $account) {
                    if ($account->getPaymentMethod() === $payment->getMethod()) {
                        $found = true;
                        break;
                    }
                }

                if (!$found) {
                    continue; // Next invoice
                }

                $number = $account->getNumber();
            } elseif ($customer) {
                $number = '1' . str_pad($customer->getId(), '7', '0', STR_PAD_LEFT);
            } else {
                $number = $this->config['default_customer'];
            }

            $credit = $invoice->getType() === InvoiceTypes::TYPE_CREDIT;

            $amount = $this->amount($invoice->getGrandTotal(), $currency);

            fputcsv($handle, [
                $date,
                $number,
                $identity,
                $credit ? null : $amount,
                $credit ? $amount : null,
                $invoice->getNumber(),
            ], ';', '"');

            // Accounts rows
            $saleTaxRule = $this->taxResolver->resolveSaleTaxRule($invoice->getSale());
            $taxesDetails = $invoice->getTaxesDetails();

            /** @var \Ekyna\Component\Commerce\Accounting\Model\AccountingInterface $account */
            foreach ($invoiceAccounts as $account) {
                if ($account->getType() === AccountingTypes::TYPE_TAX) {
                    foreach ($taxesDetails as $detail) {
                        if (0 === bccomp($detail['rate'], $account->getTax()->getRate(), 5)) {
                            $amount = $this->amount($detail['amount'], $currency);

                            if (0 === bccomp($amount, 0, 5)) {
                                continue 2; // next account
                            }

                            fputcsv($handle, [
                                $date,
                                $account->getNumber(),
                                $identity,
                                $credit ? $amount : null,
                                $credit ? null : $amount,
                                $invoice->getNumber(),
                            ], ';', '"');

                            continue 2; // next account
                        }
                    }

                    continue; // next account
                }

                $accountRule = $account->getTaxRule();
                if ($accountRule->getId() !== $saleTaxRule->getId()) {
                    continue; // next account
                }

                if ($account->getType() === AccountingTypes::TYPE_GOOD) {
                    if (null !== $tax = $account->getTax()) {
                        $amount = 0;
                        foreach ($invoice->getLinesByType(DocumentLineTypes::TYPE_GOOD) as $line) {
                            // Skip private lines
                            if ($line->getSaleItem()->isPrivate()) {
                                continue;
                            }
                            // Tax test
                            if (!in_array($tax->getRate(), $line->getTaxRates())) {
                                continue;
                            }
                            $amount += $line->getBase();
                        }

                        // Apply sale's discounts
                        $discounts = $sale->getAdjustments(AdjustmentTypes::TYPE_DISCOUNT);
                        if (!empty($discounts)) {
                            $base = $amount;
                            foreach ($sale->getAdjustments(AdjustmentTypes::TYPE_DISCOUNT) as $adjustment) {
                                $amount -= Money::round($base * $adjustment->getAmount() / 100, $currency);
                            }
                        }
                    } else {
                        $amount = $this->amount($invoice->getGoodsBase() - $invoice->getDiscountBase(), $currency);
                    }
                } elseif ($account->getType() === AccountingTypes::TYPE_SHIPPING) {
                    // TODO Check tax (?)
                    $amount = $this->amount($invoice->getShipmentBase(), $currency);
                } else {
                    throw new RuntimeException("Unexpected account type.");
                }

                if (0 === bccomp($amount, 0, 5)) {
                    continue; // next account
                }

                fputcsv($handle, [
                    $date,
                    $account->getNumber(),
                    $identity,
                    $credit ? $amount : null,
                    $credit ? null : $amount,
                    $invoice->getNumber(),
                ], ';', '"');
            }
        }

        fclose($handle);

        return $path;
    }

    /**
     * @inheritDoc
     */
    public function exportPayments(\DateTime $month)
    {
        $path = tempnam(sys_get_temp_dir(), 'acc');

        if (false === $handle = fopen($path, "w")) {
            throw new RuntimeException("Failed to open '$path' for writing.");
        }

        $accounts = $this->accountingRepository->findByTypes([
            AccountingTypes::TYPE_PAYMENT,
        ]);

        $payments = $this->paymentRepository->findByMonth($month, [
            PaymentStates::STATE_CAPTURED,
            PaymentStates::STATE_COMPLETED,
            PaymentStates::STATE_REFUNDED,
        ]);

        foreach ($payments as $payment) {
            $found = false;
            /** @var \Ekyna\Component\Commerce\Accounting\Model\AccountingInterface $account */
            foreach ($accounts as $account) {
                if ($account->getPaymentMethod() === $payment->getMethod()) {
                    $found = true;
                    break;
                }
            }

            if (!$found) {
                continue; // Next payment
            }

            $date = $payment->getCompletedAt()->format('Y-m-d'); // TODO localized ?
            $currency = $payment->getCurrency()->getCode();

            $sale = $payment->getSale();
            $customer = $sale->getCustomer();

            if ($customer) {
                $number = '1' . str_pad($customer->getId(), '7', '0', STR_PAD_LEFT);
                $identity = $customer->getFirstName() . ' ' . $customer->getLastName();
            } else {
                $number = '10000000';
                $identity = $sale->getFirstName() . ' ' . $sale->getLastName();
            }

            $credit = false; // TODO $payment->getType() === PaymentTypes::TYPE_REFUND;
            $amount = $this->amount($payment->getAmount(), $currency);

            // Payment debit
            fputcsv($handle, [
                $date,
                $account->getNumber(),
                $identity,
                $credit ? null : $amount,
                $credit ? $amount : null,
                $number,
                $payment->getNumber(),
            ], ';', '"');
            // Customer credit
            fputcsv($handle, [
                $date,
                $number,
                $identity,
                $credit ? $amount : null,
                $credit ? null : $amount,
                $number,
                $payment->getNumber(),
            ], ';', '"');

            // TODO Remove when refund payment implemented
            // Temporary : add an extra credit line for refund payments.
            if ($payment->getState() === PaymentStates::STATE_REFUNDED) {
                // Payment credit
                fputcsv($handle, [
                    $date,
                    $account->getNumber(),
                    $identity,
                    null,
                    $amount,
                    $number,
                    $payment->getNumber(),
                ], ';', '"');
                // Customer debit
                fputcsv($handle, [
                    $date,
                    $number,
                    $identity,
                    $amount,
                    null,
                    $number,
                    $payment->getNumber(),
                ], ';', '"');
            }
        }

        fclose($handle);

        return $path;
    }

    /**
     * Formats the amount.
     *
     * @param $amount
     * @param $currency
     *
     * @return float
     */
    protected function amount($amount, $currency)
    {
        // TODO currency conversion

        return Money::round($amount, $currency);
    }
}
