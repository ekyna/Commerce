<?php

namespace Ekyna\Component\Commerce\Common\Export;

use Ekyna\Component\Commerce\Common\Model\SaleInterface;

/**
 * Interface SaleExporterInterface
 * @package Ekyna\Component\Commerce\Common\Export
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface SaleExporterInterface
{
    /**
     * Exports the given sale.
     *
     * @param SaleInterface $sale
     *
     * @return string THe exported file path.
     */
    public function export(SaleInterface $sale): string;
}
