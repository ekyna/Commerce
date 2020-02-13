<?php

namespace Ekyna\Component\Commerce\Stock\Resolver;

use Ekyna\Component\Commerce\Stock\Model\StockUnitInterface;

/**
 * Interface FilterInterface
 * @package Ekyna\Component\Commerce\Stock\Resolver
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface FilterInterface
{
    /**
     * Returns false if the given stock unit must be excluded.
     *
     * @param StockUnitInterface $unit
     *
     * @return bool
     */
    public function filter(StockUnitInterface $unit): bool;
}
