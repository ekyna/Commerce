<?php

namespace Ekyna\Component\Commerce\Stock\Cache;

use Ekyna\Component\Commerce\Exception\LogicException;
use Ekyna\Component\Commerce\Stock\Model\StockUnitFinderInterface;
use Ekyna\Component\Commerce\Stock\Model\StockUnitInterface;

/**
 * Interface StockUnitCacheInterface
 * @package Ekyna\Component\Commerce\Stock\Cache
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface StockUnitCacheInterface extends StockUnitFinderInterface
{
    /**
     * Adds the stock unit to the cache.
     *
     * @param StockUnitInterface $stockUnit
     *
     * @throws LogicException
     */
    public function add(StockUnitInterface $stockUnit);

    /**
     * Removes the stock unit from the cache.
     *
     * @param StockUnitInterface $stockUnit
     *
     * @throws LogicException
     */
    public function remove(StockUnitInterface $stockUnit);
}
