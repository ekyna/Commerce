<?php

namespace Ekyna\Component\Commerce\Accounting\Export;

/**
 * Interface AccountingExporterInterface
 * @package Ekyna\Component\Commerce\Accounting\Export
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface AccountingExporterInterface
{
    /**
     * Exports invoices and payments for the given date.
     *
     * @param string      $year
     * @param string|null $month
     *
     * @return string The generated zip file path
     */
    public function export(string $year, string $month = null): string;

    /**
     * Exports invoices for the given date.
     *
     * @param \DateTime $month
     *
     * @return string The generated file path
     */
    public function exportInvoices(\DateTime $month): string;

    /**
     * Exports payments for the given date.
     *
     * @param \DateTime $month
     *
     * @return string The generated file path
     */
    public function exportPayments(\DateTime $month): string;
}
