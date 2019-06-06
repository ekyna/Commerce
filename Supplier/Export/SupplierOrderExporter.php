<?php

namespace Ekyna\Component\Commerce\Supplier\Export;

use Ekyna\Component\Commerce\Common\Util\DateUtil;
use Ekyna\Component\Commerce\Exception\RuntimeException;
use Ekyna\Component\Commerce\Supplier\Model\SupplierOrderInterface;
use Ekyna\Component\Commerce\Supplier\Repository\SupplierOrderRepositoryInterface;

/**
 * Class SupplierOrderExporter
 * @package Ekyna\Component\Commerce\Supplier\Export
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SupplierOrderExporter
{
    /**
     * @var SupplierOrderRepositoryInterface
     */
    protected $repository;


    /**
     * Constructor.
     *
     * @param SupplierOrderRepositoryInterface $repository
     */
    public function __construct(SupplierOrderRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Export the suppliers expired due orders.
     *
     * @return string The export file path.
     */
    public function exportSuppliersExpiredDueOrders()
    {
        return $this->buildFile($this->repository->findSuppliersExpiredDue(), 'suppliers_expired');
    }

    /**
     * Export the suppliers fall due orders.
     *
     * @return string The export file path.
     */
    public function exportSuppliersFallDueOrders()
    {
        return $this->buildFile($this->repository->findSuppliersFallDue(), 'suppliers_fall');
    }

    /**
     * Export the forwarders expired due orders.
     *
     * @return string The export file path.
     */
    public function exportForwardersExpiredDueOrders()
    {
        return $this->buildFile($this->repository->findForwardersExpiredDue(), 'suppliers_expired');
    }

    /**
     * Export the forwarders fall due orders.
     *
     * @return string The export file path.
     */
    public function exportForwardersFallDueOrders()
    {
        return $this->buildFile($this->repository->findForwardersFallDue(), 'suppliers_fall');
    }

    /**
     * Builds the orders export CSV file.
     *
     * @param SupplierOrderInterface[] $orders
     * @param string                   $name
     *
     * @return string
     */
    protected function buildFile(array $orders, string $name)
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

        $supplierTotal = 0;
        $forwarderTotal = 0;

        // Order rows
        foreach ($orders as $order) {
            if (!empty($row = $this->buildRow($order))) {
                fputcsv($handle, $row, ';', '"');

                $supplierTotal += $row['payment_total'];
                $forwarderTotal += $row['forwarder_total'];
            }
        }

        // Total row
        fputcsv($handle, [
            'id'                 => '',
            'number'             => '',
            'state'              => '',
            'ordered_at'         => '',
            'completed_at'       => '',
            'supplier'           => '',
            'payment_total'      => $supplierTotal,
            'payment_date'       => '',
            'payment_due_date'   => '',
            'carrier'            => '',
            'forwarder_total'    => $forwarderTotal,
            'forwarder_date'     => '',
            'forwarder_due_date' => '',
        ], ';', '"');

        fclose($handle);

        return $path;
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
            'state',
            'ordered_at',
            'completed_at',
            'supplier',
            'payment_total',
            'payment_date',
            'payment_due_date',
            'carrier',
            'forwarder_total',
            'forwarder_date',
            'forwarder_due_date',
        ];
    }

    /**
     * Builds the order row.
     *
     * @param SupplierOrderInterface $order
     *
     * @return array
     */
    protected function buildRow(SupplierOrderInterface $order)
    {
        $date = null;
        $term = null;

        if (null !== $orderedAt = $order->getOrderedAt()) {
            $orderedAt = $orderedAt->format(DateUtil::DATE_FORMAT);
        }
        if (null !== $completedAt = $order->getCompletedAt()) {
            $completedAt = $completedAt->format(DateUtil::DATE_FORMAT);
        }
        if (null !== $paymentDate = $order->getPaymentDate()) {
            $paymentDate = $paymentDate->format(DateUtil::DATE_FORMAT);
        }
        if (null !== $paymentDueDate = $order->getPaymentDueDate()) {
            $paymentDueDate = $paymentDueDate->format(DateUtil::DATE_FORMAT);
        }
        if (null !== $carrier = $order->getCarrier()) {
            $carrier = $carrier->getName();
        }
        if (null !== $forwarderDate = $order->getForwarderDate()) {
            $forwarderDate = $forwarderDate->format(DateUtil::DATE_FORMAT);
        }
        if (null !== $forwarderDueDate = $order->getForwarderDueDate()) {
            $forwarderDueDate = $forwarderDueDate->format(DateUtil::DATE_FORMAT);
        }

        return [
            'id'                 => $order->getId(),
            'number'             => $order->getNumber(),
            'state'              => $order->getState(),
            'ordered_at'         => $orderedAt,
            'completed_at'       => $completedAt,
            'supplier'           => $order->getSupplier()->getName(),
            'payment_total'      => $order->getPaymentTotal(),
            'payment_date'       => $paymentDate,
            'payment_due_date'   => $paymentDueDate,
            'carrier'            => $carrier,
            'forwarder_total'    => $order->getForwarderTotal(),
            'forwarder_date'     => $forwarderDate,
            'forwarder_due_date' => $forwarderDueDate,
        ];
    }
}
