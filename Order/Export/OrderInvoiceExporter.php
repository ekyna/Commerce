<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Order\Export;

use DateInterval;
use DatePeriod;
use DateTime;
use Ekyna\Component\Commerce\Common\Export\AbstractExporter;
use Ekyna\Component\Commerce\Common\Export\RegionProvider;
use Ekyna\Component\Commerce\Common\Util\DateUtil;
use Ekyna\Component\Commerce\Order\Model\OrderInvoiceInterface;
use Ekyna\Component\Commerce\Order\Repository\OrderInvoiceRepositoryInterface;
use Ekyna\Component\Commerce\Stat\Calculator\StatFilter;

use function sprintf;

/**
 * Class InvoiceExporter
 * @package Ekyna\Component\Commerce\Order\Export
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class OrderInvoiceExporter extends AbstractExporter
{
    /**
     * Constructor.
     *
     * @param OrderInvoiceRepositoryInterface $repository
     * @param RegionProvider $regionProvider
     */
    public function __construct(
        private readonly OrderInvoiceRepositoryInterface $repository,
        private readonly RegionProvider $regionProvider
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
     * @param DateTime $from
     * @param DateTime $to
     *
     * @return string
     */
    public function exportRegionsInvoicesStats(DateTime $from, DateTime $to): string
    {
        $period = new DatePeriod(
            (clone $from)->setTime(0, 0),
            new DateInterval('P1M'),
            (clone $to)->setTime(23, 59, 59, 999999)
        );

        $rows = [
            ['Date', 'Region', 'Grand', 'Goods', 'Discount', 'Shipping', 'Taxes'],
        ];

        $regions = $this->regionProvider->getRegions();

        $filter = $filter ?? new StatFilter();

        /** @var DateTime $date */
        foreach ($period as $date) {
            foreach ($regions as $region => $countries) {
                $filter->setCountries($countries);

                $invoices = $this->repository->findByMonthAndCountries($date, $countries);

                $grand = $goods = $discount = $shipping = $taxes = 0;
                foreach ($invoices as $invoice) {
                    if ($invoice['credit']) {
                        $grand -= $invoice['grandTotal'];
                        $goods -= $invoice['goodsBase'];
                        $discount -= $invoice['discountBase'];
                        $shipping -= $invoice['shipmentBase'];
                        $taxes -= $invoice['taxesTotal'];
                    } else {
                        $grand += $invoice['grandTotal'];
                        $goods += $invoice['goodsBase'];
                        $discount += $invoice['discountBase'];
                        $shipping += $invoice['shipmentBase'];
                        $taxes += $invoice['taxesTotal'];
                    }
                }

                $rows[] = [
                    $date->format('Y-m'),
                    $region,
                    $grand,
                    $goods,
                    $discount,
                    $shipping,
                    $taxes
                ];
            }
        }

        return $this->createFile($rows, sprintf('invoices-stats_%s_%s.csv', $from->format('Y-m'), $to->format('Y-m')));
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
