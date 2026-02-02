<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Order\Export;

use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Ekyna\Component\Commerce\Document\Model\DocumentLineTypes;
use Ekyna\Component\Commerce\Invoice\Calculator\InvoiceMarginCalculatorFactory;
use Ekyna\Component\Commerce\Order\Model\OrderInvoiceLineInterface;
use Ekyna\Component\Commerce\Order\Repository\OrderInvoiceRepositoryInterface;
use Ekyna\Component\Resource\Helper\File\Xls;
use Ekyna\Component\Resource\Model\DateRange;

use function array_map;
use function array_replace;
use function gc_collect_cycles;
use function sprintf;

/**
 * Class OrderInvoiceLineExporter
 * @package Ekyna\Component\Commerce\Order\Export
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class OrderInvoiceLineExporter
{
    public function __construct(
        private readonly EntityManagerInterface          $manager,
        private readonly OrderInvoiceRepositoryInterface $invoiceRepository,
        private readonly InvoiceMarginCalculatorFactory  $calculatorFactory,
    ) {
    }

    public function export(?DateRange $range): Xls
    {
        if (null === $range) {
            $range = new DateRange(
                new DateTime('first day of previous month'),
                new DateTime('last day of previous month')
            );
        }

        $headers = [
            'invoice_date'        => ['label' => "Invoice\nDate", 'width' => 22],
            'invoice_number'      => ['label' => "Invoice\nNumber", 'width' => 24],
            'invoice_type'        => ['label' => "Invoice\nType", 'width' => 20],
            'order_number'        => ['label' => "Order\nNumber", 'width' => 24],
            'customer_number'     => ['label' => "Customer\nNumber", 'width' => 24],
            'customer_company'    => ['label' => "Customer\nCompany", 'width' => 50],
            'customer_contact'    => ['label' => "Customer\nContact", 'width' => 40],
            'customer_group'      => ['label' => "Customer\nGroup", 'width' => 42],
            'title'               => ['label' => "Title", 'width' => 64],
            'country'             => ['label' => "Country", 'width' => 16],
            'line_type'           => ['label' => "Line\nType", 'width' => 20],
            'product_reference'   => ['label' => "Reference", 'width' => 20],
            'product_designation' => ['label' => "Designation", 'width' => 80],
            'product_quantity'    => ['label' => "Quantity", 'width' => 16],
            'revenue_product'     => ['label' => "Revenue\nProduct", 'width' => 18],
            'revenue_shipment'    => ['label' => "Revenue\nShipment", 'width' => 18],
            'cost_product'        => ['label' => "Cost\nProduct", 'width' => 18],
            'cost_supply'         => ['label' => "Cost\nSupply", 'width' => 18],
            'cost_shipment'       => ['label' => "Cost\nShipment", 'width' => 18],
        ];

        $file = new Xls(
            sprintf(
                'invoices_lines_%s_%s',
                $range->getStart()->format('Y-m-d'),
                $range->getEnd()->format('Y-m-d')
            )
        );

        $file->setHeaders(array_map(fn($col): string => $col['label'], $headers));
        $file->setColumnsWidths(array_map(fn($col): int => $col['width'], $headers));
        for ($i = 1; $i <= 19; $i++) {
            $file->getSheet()->getStyle([$i, 1])->getAlignment()->setWrapText(true);
        }

        $calculator = $this->calculatorFactory->create();

        $page = 0;
        while (!empty($invoices = $this->invoiceRepository->findByCreatedAt($range, $page, 30))) {
            foreach ($invoices as $invoice) {
                $customer = array_replace([
                    'number'     => null,
                    'company'    => null,
                    'first_name' => null,
                    'last_name'  => null,
                ], $invoice->getCustomer());
                $order = $invoice->getOrder();

                $address = $invoice->getCustomInvoiceAddress() ?? $invoice->getInvoiceAddress();

                $data = [
                    'invoice_date'        => $invoice->getCreatedAt()->format('Y-m-d'),
                    'invoice_number'      => $invoice->getNumber(),
                    'invoice_type'        => $invoice->isCredit() ? 'credit' : 'invoice',
                    'order_number'        => $order->getNumber(),
                    'customer_number'     => $customer['number'],
                    'customer_company'    => $customer['company'],
                    'customer_contact'    => $customer['first_name'] . ' ' . $invoice->getCustomer()['last_name'],
                    'customer_group'      => $order->getCustomerGroup(),
                    'title'               => $order->getTitle(),
                    'country'             => $address['country'],
                    'line_type'           => null,
                    'product_reference'   => null,
                    'product_designation' => null,
                    'product_quantity'    => null,
                    'revenue_product'     => null,
                    'revenue_shipment'    => null,
                    'cost_product'        => null,
                    'cost_supply'         => null,
                    'cost_shipment'       => null,
                ];

                /** @var OrderInvoiceLineInterface $line */
                foreach ($invoice->getLines() as $line) {
                    if ($line->getType() === DocumentLineTypes::TYPE_GOOD) {
                        $item = $line->getOrderItem();

                        if ($item->isPrivate()) {
                            continue;
                        }
                        if ($item->isCompound() && !$item->hasPrivateChildren()) {
                            continue;
                        }
                    }

                    $margin = $calculator->calculateInvoiceLine($line);

                    $file->addRow(array_replace($data, [
                        'line_type'           => $line->getType(),
                        'product_reference'   => $line->getReference(),
                        'product_designation' => $line->getDesignation(),
                        'product_quantity'    => $line->getQuantity()->toFixed(),
                        'revenue_product'     => $margin->getRevenueProduct()->toFixed(2),
                        'revenue_shipment'    => $margin->getRevenueShipment()->toFixed(2),
                        'cost_product'        => $margin->getCostProduct()->toFixed(2),
                        'cost_supply'         => $margin->getCostSupply()->toFixed(2),
                        'cost_shipment'       => $margin->getCostShipment()->toFixed(2),
                    ]));
                }
            }

            unset($invoices, $invoice, $order, $item, $margin);

            $this->manager->clear();
            gc_collect_cycles();

            $page++;
        }

        return $file;
    }
}
