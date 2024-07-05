<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Order\Export;

use Ekyna\Component\Commerce\Common\Export\AbstractExporter;
use Ekyna\Component\Commerce\Common\Util\DateUtil;
use Ekyna\Component\Commerce\Order\Model\OrderInvoiceInterface;
use Ekyna\Component\Commerce\Order\Repository\OrderInvoiceRepositoryInterface;

/**
 * Class InvoiceExporter
 * @package Ekyna\Component\Commerce\Order\Export
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class OrderInvoiceExporter extends AbstractExporter
{
    public function __construct(
        private readonly OrderInvoiceRepositoryInterface $repository
    ) {
        parent::__construct();
    }

    /**
     * Exports due invoices.
     *
     * @return string The export file path.
     */
    public function exportDueInvoices(): string
    {
        return $this->buildFile($this->repository->findDueInvoices(), 'invoices_due', $this->getDefaultMap());
    }

    /**
     * Exports fall invoices.
     *
     * @return string The export file path.
     */
    public function exportFallInvoices(): string
    {
        return $this->buildFile($this->repository->findFallInvoices(), 'invoices_fall', $this->getDefaultMap());
    }

    /**
     * Returns the default fields map.
     *
     * @return array
     */
    private function getDefaultMap(): array
    {
        return [
            'date'           => function (OrderInvoiceInterface $invoice): string {
                return $invoice->getCreatedAt()->format(DateUtil::DATE_FORMAT);
            },
            'number'         => 'number',
            'order_date'     => function (OrderInvoiceInterface $invoice): string {
                return $invoice->getOrder()->getCreatedAt()->format(DateUtil::DATE_FORMAT);
            },
            'order_number'   => 'order.number',
            'voucher_number' => 'order.voucherNumber',
            'company'        => 'order.company',
            'grand_total'    => 'grandTotal',
            'paid_total'     => 'paidTotal',
            'due_date'       => function (OrderInvoiceInterface $invoice): ?string {
                if (null !== $date = $invoice->getDueDate()) {
                    return $date->format(DateUtil::DATE_FORMAT);
                }

                return null;
            },
            'payment_term'   => function (OrderInvoiceInterface $invoice) {
                if (null !== $term = $invoice->getOrder()->getPaymentTerm()) {
                    return $term->getName();
                }

                return null;
            },
            'order_id'       => 'order.id',
            'payment_state'  => 'order.paymentState',
        ];
    }
}
