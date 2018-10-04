<?php

namespace Ekyna\Component\Commerce\Order\Export;

use Ekyna\Component\Commerce\Common\Util\Formatter;
use Ekyna\Component\Commerce\Exception\RuntimeException;
use Ekyna\Component\Commerce\Order\Model\OrderInterface;
use Ekyna\Component\Commerce\Order\Repository\OrderRepositoryInterface;

/**
 * Class OrderExporter
 * @package Ekyna\Component\Commerce\Order\Export
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class OrderExporter
{
    /**
     * @var OrderRepositoryInterface
     */
    protected $repository;

    /**
     * @var Formatter
     */
    protected $formatter;


    /**
     * Constructor.
     *
     * @param OrderRepositoryInterface $repository
     * @param Formatter                $formatter
     */
    public function __construct(OrderRepositoryInterface $repository, Formatter $formatter)
    {
        $this->repository = $repository;
        $this->formatter = $formatter;
    }

    /**
     * Export the due orders.
     *
     * @return string The export file path.
     */
    public function exportDueOrders()
    {
        $orders = $this->repository->findDueOrders();

        $path = tempnam(sys_get_temp_dir(), 'due');

        if (false === $handle = fopen($path, "w")) {
            throw new RuntimeException("Failed to open '$path' for writing.");
        }

        if (!empty($headers = $this->buildHeaders())) {
            fputcsv($handle, $headers, ';', '"');
        }

        $total = 0;
        $expired = 0;

        // Order rows
        foreach ($orders as $order) {
            if (!empty($row = $this->buildRow($order))) {
                fputcsv($handle, $row, ';', '"');

                $total += $row['due_amount'];
                $expired += $row['outstanding_expired'];
            }
        }

        // Total row
        fputcsv($handle, [
            'id'                  => '',
            'number'              => '',
            'company'             => '',
            'payment_state'       => '',
            'shipment_state'      => '',
            'invoice_state'       => '',
            'payment_term'       => '',
            'due_amount'          => $total,
            'outstanding_expired' => $expired,
            'outstanding_date'    => '',
            'created_at'          => '',
        ], ';', '"');

        fclose($handle);

        return $path;
    }

    /**
     * Returns the due orders.
     *
     * @return OrderInterface[]
     */
    protected function findDueOrders()
    {
        return $this->repository->findDueOrders();
    }

    /**
     * Returns the headers.
     *
     * @return array
     */
    protected function buildHeaders()
    {
        return [
            'id',
            'number',
            'company',
            'payment_state',
            'shipment_state',
            'invoice_state',
            'payment_term',
            'due_amount',
            'outstanding_expired',
            'outstanding_date',
            'created_at',
        ];
    }

    /**
     * Builds the order row.
     *
     * @param OrderInterface $order
     *
     * @return array
     */
    protected function buildRow(OrderInterface $order)
    {
        $date = null;
        $term = null;

        if (null !== $date = $order->getOutstandingDate()) {
            $date = $this->formatter->date($date);
        }
        if (null !== $term = $order->getPaymentTerm()) {
            $term = $term->getName();
        }

        return [
            'id'                  => $order->getId(),
            'number'              => $order->getNumber(),
            'company'             => $order->getCompany(),
            'payment_state'       => $order->getPaymentState(),
            'shipment_state'      => $order->getShipmentState(),
            'invoice_state'       => $order->getInvoiceState(),
            'payment_term'        => $term,
            'due_amount'          => $order->getGrandTotal() - $order->getPaidTotal(),
            'outstanding_expired' => $order->getOutstandingExpired(),
            'outstanding_date'    => $date,
            'created_at'          => $this->formatter->date($order->getCreatedAt()),
        ];
    }
}
