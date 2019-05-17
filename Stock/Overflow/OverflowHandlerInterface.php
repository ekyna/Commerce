<?php

namespace Ekyna\Component\Commerce\Stock\Overflow;

use Ekyna\Component\Commerce\Exception\StockLogicException;
use Ekyna\Component\Commerce\Stock\Model\StockUnitInterface;

/**
 * Interface OverflowHandlerInterface
 * @package Ekyna\Component\Commerce\Stock\Overflow
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface OverflowHandlerInterface
{
    /**
     * Checks stock unit overflow (sold > ordered + adjusted) and fixes assignments if needed.
     *
     * @param StockUnitInterface $stockUnit
     *
     * @return bool Whether assignment(s) has been moved.
     *
     * @throws StockLogicException
     */
    public function handle(StockUnitInterface $stockUnit): bool;
}
