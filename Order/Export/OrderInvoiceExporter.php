<?php

namespace Ekyna\Component\Commerce\Order\Export;

use Ekyna\Component\Commerce\Common\Util\DateUtil;
use Ekyna\Component\Commerce\Common\Util\Money;
use Ekyna\Component\Commerce\Exception\RuntimeException;
use Ekyna\Component\Commerce\Order\Model\OrderInvoiceInterface;
use Ekyna\Component\Commerce\Order\Repository\OrderInvoiceRepositoryInterface;

/**
 * Class InvoiceExporter
 * @package Ekyna\Component\Commerce\Order\Export
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class OrderInvoiceExporter
{
    /**
     * @var OrderInvoiceRepositoryInterface
     */
    protected $repository;


    /**
     * Constructor.
     *
     * @param OrderInvoiceRepositoryInterface $repository
     */
    public function __construct(OrderInvoiceRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Exports due invoices.
     *
     * @return string The export file path.
     */
    public function exportDueInvoices(): string
    {
        return $this->buildFile($this->repository->findDueInvoices(), 'invoices_due');
    }

    /**
     * Exports fall invoices.
     *
     * @return string The export file path.
     */
    public function exportFallInvoices(): string
    {
        return $this->buildFile($this->repository->findFallInvoices(), 'invoices_fall');
    }

    /**
     * Builds the orders export CSV file.
     *
     * @param OrderInvoiceInterface[] $invoices
     * @param string                  $name
     *
     * @return string
     */
    protected function buildFile(array $invoices, string $name): string
    {
        if (false === $path = tempnam(sys_get_temp_dir(), $name)) {
            throw new RuntimeException("Failed to create temporary file.");
        }

        if (false === $handle = fopen($path, "w")) {
            throw new RuntimeException("Failed to open '$path' for writing.");
        }

        if (!empty($headers = $this->buildHeaders())) {
            fputcsv($handle, $headers, ';', '"');
        }

        $grandTotal = 0;
        $paidTotal = 0;

        // Order rows
        foreach ($invoices as $invoice) {
            if (!empty($row = $this->buildRow($invoice))) {
                fputcsv($handle, $row, ';', '"');

                $grandTotal += $row['grand_total'];
                $paidTotal += $row['paid_total'];
            }
        }

        // Total row
        fputcsv($handle, [
            'date'           => '',
            'number'         => '',
            'order_date'     => '',
            'order_number'   => '',
            'voucher_number' => '',
            'company'        => '',
            'grand_total'    => $grandTotal,
            'paid_total'     => $paidTotal,
            'currency'       => '',
            'due_date'       => '',
            'payment_term'   => '',
        ], ';', '"');

        fclose($handle);

        return $path;
    }

    /**
     * Returns the headers.
     *
     * @return array
     */
    protected function buildHeaders(): array
    {
        return [
            'date',
            'number',
            'order_date',
            'order_number',
            'voucher_number',
            'company',
            'grand_total',
            'paid_total',
            'currency',
            'due_date',
            'payment_term',
        ];
    }

    /**
     * Builds the order row.
     *
     * @param OrderInvoiceInterface $invoice
     *
     * @return array|null
     */
    protected function buildRow(OrderInvoiceInterface $invoice): ?array
    {
        $currency = $invoice->getCurrency();

        if (1 !== Money::compare($invoice->getGrandTotal(), $invoice->getPaidTotal(), $currency)) {
            return null;
        }

        $order = $invoice->getOrder();

        if (null !== $dueDate = $invoice->getDueDate()) {
            $dueDate = $dueDate->format(DateUtil::DATE_FORMAT);
        }
        if (null !== $term = $order->getPaymentTerm()) {
            $term = $term->getName();
        }

        return [
            'date'           => $invoice->getCreatedAt()->format(DateUtil::DATE_FORMAT),
            'number'         => $invoice->getNumber(),
            'order_date'     => $order->getCreatedAt()->format(DateUtil::DATE_FORMAT),
            'order_number'   => $order->getNumber(),
            'voucher_number' => $order->getVoucherNumber(),
            'company'        => $order->getCompany(),
            'grand_total'    => $invoice->getGrandTotal(),
            'paid_total'     => $invoice->getPaidTotal(),
            'currency'       => $invoice->getCurrency(),
            'due_date'       => $dueDate,
            'payment_term'   => $term,
        ];
    }
}
