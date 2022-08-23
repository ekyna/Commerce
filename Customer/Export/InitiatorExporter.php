<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Customer\Export;

use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Customer\Model\CustomerInterface;
use Ekyna\Component\Commerce\Order\Repository\OrderRepositoryInterface;
use Ekyna\Component\Commerce\Quote\Repository\QuoteRepositoryInterface;
use Ekyna\Component\Resource\Helper\File\Csv;

use function trim;

/**
 * Class InitiatorExporter
 * @package Ekyna\Component\Commerce\Customer\Export
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class InitiatorExporter
{
    public function __construct(
        protected readonly OrderRepositoryInterface $orderRepository,
        protected readonly QuoteRepositoryInterface $quoteRepository,
    ) {
    }

    public function exportOrders(CustomerInterface $initiator): Csv
    {
        $sales = $this->orderRepository->findByInitiatorCustomer($initiator);

        return $this->export($sales, $initiator->getNumber() . '-orders.csv');
    }

    public function exportQuotes(CustomerInterface $initiator): Csv
    {
        $sales = $this->quoteRepository->findByInitiatorCustomer($initiator);

        return $this->export($sales, $initiator->getNumber() . '-quotes.csv');
    }

    /**
     * @param array<SaleInterface> $sales
     */
    protected function export(array $sales, string $name): Csv
    {
        $csv = Csv::create($name);

        $csv->addRow($this->buildHeaders());

        foreach ($sales as $sale) {
            $csv->addRow($this->buildRow($sale));
        }

        return $csv;
    }

    protected function buildHeaders(): array
    {
        return [
            'Number',
            'Date',
            'Company',
            'Customer',
            'ATI total',
            'Status',
        ];
    }

    protected function buildRow(SaleInterface $sale): array
    {
        return [
            'number'   => $sale->getNumber(),
            'date'     => $sale->getAcceptedAt()?->format('Y-m-d'),
            'company'  => $sale->getCompany(),
            'customer' => trim($sale->getFirstName() . ' ' . $sale->getLastName()),
            'total'    => $sale->getGrandTotal()->toFixed(2),
            'status'   => $sale->getState(),
        ];
    }
}
