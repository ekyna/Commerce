<?php

namespace Ekyna\Component\Commerce\Stock\Manager;

use Ekyna\Component\Commerce\Stock\Model\StockUnitInterface;

/**
 * Interface StockUnitManagerInterface
 * @package Ekyna\Component\Commerce\Stock\Manager
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface StockUnitManagerInterface
{
    /**
     * Persists or removes the stock unit.
     *
     * @param StockUnitInterface $stockUnit
     */
    public function persistOrRemove(StockUnitInterface $stockUnit): void;
}
