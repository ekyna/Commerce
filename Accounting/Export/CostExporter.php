<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Accounting\Export;

use DateTime;
use Ekyna\Component\Commerce\Common\Currency\CurrencyConverterInterface;
use Ekyna\Component\Commerce\Exception\RuntimeException;
use Ekyna\Component\Commerce\Invoice\Calculator\InvoiceCalculatorInterface;
use Ekyna\Component\Commerce\Invoice\Calculator\InvoiceCostCalculator;
use Ekyna\Component\Commerce\Invoice\Repository\InvoiceRepositoryInterface;
use Ekyna\Component\Commerce\Stock\Model\StockAdjustmentReasons;
use Ekyna\Component\Commerce\Stock\Repository\StockAdjustmentRepositoryInterface;
use ZipArchive;

use function fclose;
use function fopen;
use function fputcsv;
use function ini_set;
use function sprintf;
use function sys_get_temp_dir;
use function tempnam;

/**
 * Class CostExporter
 * @package Ekyna\Component\Commerce\Invoice\Export
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class CostExporter
{
    protected InvoiceRepositoryInterface         $invoiceRepository;
    protected StockAdjustmentRepositoryInterface $adjustmentRepository;
    protected CurrencyConverterInterface         $currencyConverter;
    protected InvoiceCalculatorInterface         $invoiceCalculator;
    protected InvoiceCostCalculator              $costCalculator;
    protected bool                               $debug;


    public function __construct(
        InvoiceRepositoryInterface         $invoiceRepository,
        StockAdjustmentRepositoryInterface $adjustmentRepository,
        CurrencyConverterInterface         $currencyConverter,
        InvoiceCalculatorInterface         $invoiceCalculator,
        InvoiceCostCalculator              $costCalculator,
        bool                               $debug
    ) {
        $this->invoiceRepository = $invoiceRepository;
        $this->adjustmentRepository = $adjustmentRepository;
        $this->currencyConverter = $currencyConverter;
        $this->invoiceCalculator = $invoiceCalculator;
        $this->costCalculator = $costCalculator;
        $this->debug = $debug;
    }

    public function export(string $year, string $month = null): string
    {
        ini_set('max_execution_time', '0');

        $months = ExportUtil::buildMonthList($year, $month);

        $path = tempnam(sys_get_temp_dir(), 'costs');

        $zip = new ZipArchive();

        if (false === $zip->open($path, ZipArchive::OVERWRITE)) {
            throw new RuntimeException("Failed to open '$path' for writing.");
        }

        foreach ($months as $month) {
            $zip->addFile($this->exportInvoices($month), sprintf('%s_invoices.csv', $month->format('Y-m')));
            $zip->addFile($this->exportAdjustment($month), sprintf('%s_adjustments.csv', $month->format('Y-m')));
        }

        $zip->close();

        return $path;
    }

    protected function exportInvoices(DateTime $month): string
    {
        $path = tempnam(sys_get_temp_dir(), 'invoices_costs');

        if (false === $handle = fopen($path, 'w')) {
            throw new RuntimeException("Failed to open '$path' for writing.");
        }

        $data = [
            'Number',
            'Ati Total',
            'Goods Cost',
            'Shipping Cost',
        ];

        if ($this->debug) {
            $data[] = 'Order';
        }

        if (false === fputcsv($handle, $data, ';')) {
            throw new RuntimeException('Failed to write line.');
        }

        $currency = $this->currencyConverter->getDefaultCurrency();

        $invoices = $this->invoiceRepository->findByMonth($month);

        foreach ($invoices as $invoice) {
            if ($invoice->getCurrency() !== $currency) {
                $invoice = clone $invoice;
                $invoice->setCurrency($currency);

                $this->invoiceCalculator->calculate($invoice); // TODO Check id (clone)
            }

            $cost = $this->costCalculator->calculate($invoice);

            $netTotal = $invoice->getGrandTotal() - $invoice->getTaxesTotal();

            if ($invoice->isCredit()) {
                $data = [
                    $invoice->getNumber(),
                    -$netTotal,
                    -$cost->getGood(),
                    -$cost->getShipping(),
                ];
            } else {
                $data = [
                    $invoice->getNumber(),
                    $netTotal,
                    $cost->getGood(),
                    $cost->getShipping(),
                ];
            }

            if ($this->debug) {
                $data[] = $invoice->getSale()->getId();
            }

            if (false === fputcsv($handle, $data, ';')) {
                throw new RuntimeException('Failed to write line.');
            }
        }

        fclose($handle);

        return $path;
    }

    protected function exportAdjustment(DateTime $month): string
    {
        $path = tempnam(sys_get_temp_dir(), 'adjustments_costs');

        if (false === $handle = fopen($path, 'w')) {
            throw new RuntimeException("Failed to open '$path' for writing.");
        }

        $data = [
            'Reference',
            'Goods Cost',
            'Shipping Cost',
        ];

        if ($this->debug) {
            $data[] = 'Subject';
        }

        if (false === fputcsv($handle, $data, ';')) {
            throw new RuntimeException('Failed to write line.');
        }

        $adjustments = $this->adjustmentRepository->findByMonth($month);

        foreach ($adjustments as $adjustment) {
            $unit = $adjustment->getStockUnit();

            if (StockAdjustmentReasons::isDebitReason($adjustment->getReason())) {
                $data = [
                    $unit->getSubject()->getReference(),
                    -$unit->getNetPrice() * $adjustment->getQuantity(),
                    -$unit->getShippingPrice() * $adjustment->getQuantity(),
                ];
            } else {
                $data = [
                    $unit->getSubject()->getReference(),
                    $unit->getNetPrice() * $adjustment->getQuantity(),
                    $unit->getShippingPrice() * $adjustment->getQuantity(),
                ];
            }

            if ($this->debug) {
                $data[] = $unit->getSubject()->getId();
            }

            if (false === fputcsv($handle, $data, ';')) {
                throw new RuntimeException('Failed to write line.');
            }
        }

        fclose($handle);

        return $path;
    }
}
