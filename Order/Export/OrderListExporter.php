<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Order\Export;

use Ekyna\Component\Commerce\Common\Export\AbstractExporter;
use Ekyna\Component\Commerce\Common\Util\DateUtil;
use Ekyna\Component\Commerce\Common\Util\Money;
use Ekyna\Component\Commerce\Exception\RuntimeException;
use Ekyna\Component\Commerce\Order\Model\OrderInterface;
use Ekyna\Component\Commerce\Order\Repository\OrderRepositoryInterface;
use ZipArchive;

/**
 * Class OrderListExporter
 * @package Ekyna\Component\Commerce\Order\Export
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class OrderListExporter extends AbstractExporter
{
    protected OrderRepositoryInterface $repository;

    public function __construct(OrderRepositoryInterface $repository)
    {
        parent::__construct();

        $this->repository = $repository;
    }

    /**
     * Export the due orders.
     *
     * @return string The export file path.
     */
    public function exportDueOrders(): string
    {
        return $this->buildFile(
            $this->repository->findDueOrders(),
            'due',
            $this->getDefaultMap()
        );
    }

    /**
     * Export all the due orders (archive with all CSV files).
     *
     * @return string The export file path.
     */
    public function exportAllDueOrders(): string
    {
        $path = tempnam(sys_get_temp_dir(), 'acc');

        $zip = new ZipArchive();

        if (false === $zip->open($path, ZipArchive::OVERWRITE)) {
            throw new RuntimeException("Failed to open '$path' for writing.");
        }

        $zip->addFile($this->exportRegularDueOrders(), 'regular-due-orders.csv');
        $zip->addFile($this->exportOutstandingExpiredDueOrders(), 'outstanding-expired-due-orders.csv');
        $zip->addFile($this->exportOutstandingFallDueOrders(), 'outstanding-fall-due-orders.csv');
        $zip->addFile($this->exportOutstandingPendingDueOrders(), 'outstanding-pending-due-orders.csv');

        $zip->close();

        return $path;
    }

    /**
     * Export the regular (payment term less) due orders.
     *
     * @return string The export file path.
     */
    public function exportRegularDueOrders(): string
    {
        return $this->buildFile(
            $this->repository->getRegularDueOrders(),
            'regular_due',
            $this->getDefaultMap()
        );
    }

    /**
     * Export the outstanding expired due orders.
     *
     * @return string The export file path.
     */
    public function exportOutstandingExpiredDueOrders(): string
    {
        return $this->buildFile(
            $this->repository->getOutstandingExpiredDueOrders(),
            'outstanding_expired_due',
            $this->getDefaultMap()
        );
    }

    /**
     * Export the outstanding fall due orders.
     *
     * @return string The export file path.
     */
    public function exportOutstandingFallDueOrders(): string
    {
        return $this->buildFile(
            $this->repository->getOutstandingFallDueOrders(),
            'outstanding_fall_due',
            $this->getDefaultMap()
        );
    }

    /**
     * Export the outstanding pending due orders.
     *
     * @return string The export file path.
     */
    public function exportOutstandingPendingDueOrders(): string
    {
        return $this->buildFile(
            $this->repository->getOutstandingPendingDueOrders(),
            'outstanding_pending_due',
            $this->getDefaultMap()
        );
    }

    /**
     * Export the remaining orders (that needs to be invoiced).
     *
     * @return string The export file path.
     */
    public function exportRemainingOrders(): string
    {
        return $this->buildFile(
            $this->repository->getRemainingOrders(),
            'remaining',
            $this->getRemainingMap()
        );
    }

    /**
     * Returns the default fields map.
     *
     * @return array
     */
    private function getDefaultMap(): array
    {
        return [
            //'id'                  => 'id',
            'number'              => 'number',
            'company'             => 'company',
            'payment_state'       => 'paymentState',
            'shipment_state'      => 'shipmentState',
            'invoice_state'       => 'invoiceState',
            'payment_term'        => function (OrderInterface $order) {
                if ($term = $order->getPaymentTerm()) {
                    return $term->getName();
                }

                return null;
            },
            'due_amount'          => function (OrderInterface $order): string {
                $currency = $order->getCurrency()->getCode();

                if ($order->hasInvoices()) {
                    return Money::fixed(
                        $order->getInvoiceTotal() - $order->getCreditTotal()
                        - $order->getPaidTotal() + $order->getRefundedTotal(),
                        $currency
                    );
                }

                return Money::fixed(
                    $order->getGrandTotal() - $order->getPaidTotal(),
                    $currency
                );
            },
            'outstanding_expired' => 'outstandingExpired',
            'outstanding_date'    => function (OrderInterface $order): ?string {
                if ($date = $order->getOutstandingDate()) {
                    return $date->format(DateUtil::DATE_FORMAT);
                }

                return null;
            },
            'created_at'          => function (OrderInterface $order): string {
                return $order->getCreatedAt()->format(DateUtil::DATE_FORMAT);
            },
        ];
    }

    /**
     * Returns the default fields map.
     *
     * @return array
     */
    private function getRemainingMap(): array
    {
        return [
            'date'           => function (OrderInterface $order): string {
                return $order->getCreatedAt()->format(DateUtil::DATE_FORMAT);
            },
            'number'         => 'number',
            'voucher_number' => 'voucherNumber',
            'company'        => 'company',
            'grand_total'    => 'grandTotal',
            'paid_total'     => 'paidTotal',
            'invoice_total'  => 'invoiceTotal',
            'due_date'       => function (OrderInterface $order): ?string {
                if (null !== $date = $order->getOutstandingDate()) {
                    return $date->format(DateUtil::DATE_FORMAT);
                }

                return null;
            },
            'payment_term'   => function (OrderInterface $order) {
                if (null !== $term = $order->getPaymentTerm()) {
                    return $term->getName();
                }

                return null;
            },
        ];
    }
}
