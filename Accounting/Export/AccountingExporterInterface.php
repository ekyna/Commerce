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
     * @param \DateTime $month
     *
     * @return string The generated zip file path
     */
    public function export(\DateTime $month);

    /**
     * Exports invoices for the given date.
     *
     * @param \DateTime $month
     *
     * @return string The generated file path
     */
    public function exportInvoices(\DateTime $month);

    /**
     * Exports payments for the given date.
     *
     * @param \DateTime $month
     *
     * @return string The generated file path
     */
    public function exportPayments(\DateTime $month);
}