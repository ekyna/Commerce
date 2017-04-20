<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Accounting\Export;

use DateTimeInterface;
use Ekyna\Component\Commerce\Exception\LogicException;

/**
 * Interface AccountingExporterInterface
 * @package Ekyna\Component\Commerce\Accounting\Export
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface AccountingExporterInterface
{
    /**
     * Adds the given filter.
     *
     * @throws LogicException
     */
    public function addFilter(AccountingFilterInterface $filter): void;

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
     * @return string The generated file path
     */
    public function exportInvoices(DateTimeInterface $month): string;

    /**
     * Exports payments for the given date.
     *
     * @return string The generated file path
     */
    public function exportPayments(DateTimeInterface $month): string;
}
