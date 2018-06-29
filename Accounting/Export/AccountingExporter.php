<?php

namespace Ekyna\Component\Commerce\Accounting\Export;

use Ekyna\Component\Commerce\Accounting\Model\AccountingTypes;
use Ekyna\Component\Commerce\Common\Util\Money;
use Ekyna\Component\Commerce\Exception\RuntimeException;
use Ekyna\Component\Commerce\Invoice\Model\InvoiceTypes;
use Ekyna\Component\Commerce\Invoice\Repository\InvoiceRepositoryInterface;
use Ekyna\Component\Commerce\Pricing\Resolver\TaxResolverInterface;
use Ekyna\Component\Resource\Doctrine\ORM\ResourceRepositoryInterface;

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
     * @var ResourceRepositoryInterface
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
     * Constructor.
     *
     * @param InvoiceRepositoryInterface  $invoiceRepository
     * @param ResourceRepositoryInterface $accountingRepository
     * @param TaxResolverInterface        $taxResolver
     */
    public function __construct(
        InvoiceRepositoryInterface $invoiceRepository,
        ResourceRepositoryInterface $accountingRepository,
        TaxResolverInterface $taxResolver
    ) {
        $this->invoiceRepository = $invoiceRepository;
        $this->accountingRepository = $accountingRepository;
        $this->taxResolver = $taxResolver;
    }

    /**
     * @inheritDoc
     */
    public function export(\DateTime $month)
    {
        $path = tempnam(sys_get_temp_dir(), 'acc');

        if (false === $handle = fopen($path, "w")) {
            throw new RuntimeException("Failed to open '$path' for writing.");
        }

        $accounts = $this->accountingRepository->findAll();
        $invoices = $this->invoiceRepository->findByMonth($month);

        foreach ($invoices as $invoice) {
            $date = $invoice->getCreatedAt()->format('Y-m-d'); // TODO localized ?
            $currency = $invoice->getCurrency();

            $sale = $invoice->getSale();
            $customer = $sale->getCustomer();

            // Grand total row
            if ($customer) {
                $number = '1' . str_pad($customer->getId(), '7', '0', STR_PAD_LEFT);
                $identity = $customer->getFirstName() . ' ' . $customer->getLastName();
            } else {
                $number = '10000000';
                $identity = $sale->getFirstName() . ' ' . $sale->getLastName();
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
            $rule = $this->taxResolver->resolveSaleTaxRule($invoice->getSale());
            $taxesDetails = $invoice->getTaxesDetails();

            /** @var \Ekyna\Component\Commerce\Accounting\Model\AccountingInterface $account */
            foreach ($accounts as $account) {
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
                if ($accountRule->getId() !== $rule->getId()) {
                    continue; // next account
                }

                if ($account->getType() === AccountingTypes::TYPE_GOOD) {
                    $amount = $this->amount($invoice->getGoodsBase() - $invoice->getDiscountBase(), $currency);
                } elseif ($account->getType() === AccountingTypes::TYPE_SHIPPING) {
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
