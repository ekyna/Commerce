<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Stock\Resolver;

use Ekyna\Component\Commerce\Stock\Model\StockUnitInterface;

/**
 * Interface StockUnitStateResolverInterface
 * @package Ekyna\Component\Commerce\Stock\Resolver
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface StockUnitStateResolverInterface
{
    /**
     * Resolves the stock unit state.
     *
     * @param StockUnitInterface $stockUnit
     *
     * @return bool Whether the states has been changed
     */
    public function resolve(StockUnitInterface $stockUnit): bool;
}
