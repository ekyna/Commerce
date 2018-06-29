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
     * Exports accounting for the given date.
     *
     * @param \DateTime $month
     *
     * @return string The generated file path
     */
    public function export(\DateTime $month);
}