<?php

declare(strict_types=1);

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
     * @return string The exported file path.
     */
    public function export(SaleInterface $sale, bool $internal = false): string;
}
