<?php

namespace Ekyna\Component\Commerce\Order\Export;

use Ekyna\Component\Commerce\Common\Util\DateUtil;
use Ekyna\Component\Commerce\Common\Util\Money;
use Ekyna\Component\Commerce\Exception\RuntimeException;
use Ekyna\Component\Commerce\Invoice\Resolver\InvoicePaymentResolverInterface;
use Ekyna\Component\Commerce\Order\Model\OrderInvoiceInterface;
use Ekyna\Component\Commerce\Order\Repository\OrderInvoiceRepositoryInterface;
use Ekyna\Component\Commerce\Supplier\Model\SupplierOrderInterface;

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
     * @var InvoicePaymentResolverInterface
     */
    protected $resolver;


    /**
     * Constructor.
     *
     * @param OrderInvoiceRepositoryInterface $repository
     * @param InvoicePaymentResolverInterface $resolver
     */
    public function __construct(OrderInvoiceRepositoryInterface $repository, InvoicePaymentResolverInterface $resolver)
    {
        $this->repository = $repository;
        $this->resolver = $resolver;
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
     * @param SupplierOrderInterface[] $orders
     * @param string                   $name
     *
     * @return string
     */
    protected function buildFile(array $orders, string $name): string
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
        foreach ($orders as $order) {
            if (!empty($row = $this->buildRow($order))) {
                fputcsv($handle, $row, ';', '"');

                $grandTotal += $row['grand_total'];
                $paidTotal += $row['paid_total'];
            }
        }

        // Total row
        fputcsv($handle, [
            'id'             => '',
            'number'         => '',
            'currency'       => '',
            'grand_total'    => $grandTotal,
            'paid_total'     => $paidTotal,
            'due_date'       => '',
            'order_id'       => '',
            'order_number'   => '',
            'company'        => '',
            'payment_state'  => '',
            'shipment_state' => '',
            'invoice_state'  => '',
            'payment_term'   => '',
            'order_date'     => '',
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
            'id',
            'number',
            'currency',
            'grand_total',
            'paid_total',
            'due_date',
            'order_id',
            'order_number',
            'company',
            'payment_state',
            'shipment_state',
            'invoice_state',
            'payment_term',
            'order_date',
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
        $grandTotal = $invoice->getGrandTotal();
        $paidTotal = $this->resolver->getPaidTotal($invoice);

        if (1 !== Money::compare($grandTotal, $paidTotal, $currency)) {
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
            'id'             => $order->getId(),
            'number'         => $invoice->getNumber(),
            'currency'       => $invoice->getCurrency(),
            'grand_total'    => $grandTotal,
            'paid_total'     => $paidTotal,
            'due_date'       => $dueDate,
            'order_id'       => $order->getId(),
            'order_number'   => $order->getNumber(),
            'company'        => $order->getCompany(),
            'payment_state'  => $order->getPaymentState(),
            'shipment_state' => $order->getShipmentState(),
            'invoice_state'  => $order->getInvoiceState(),
            'payment_term'   => $term,
            'order_date'     => $order->getCreatedAt()->format(DateUtil::DATE_FORMAT),
        ];
    }
}
