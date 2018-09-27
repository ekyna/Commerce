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

        foreach ($orders as $order) {
            if (!empty($row = $this->buildRow($order))) {
                fputcsv($handle, $row, ';', '"');
            }
        }

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
            'number',
            'company',
            'payment state',
            'due total',
            'due date',
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

        if (null !== $date = $order->getOutstandingDate()) {
            $date = $this->formatter->date($date);
        }

        return [
            $order->getNumber(),
            $order->getCompany(),
            $order->getPaymentState(),
            $order->getGrandTotal() - $order->getPaidTotal(),
            $date,
        ];
    }
}
