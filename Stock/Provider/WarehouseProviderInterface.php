<?php

namespace Ekyna\Component\Commerce\Stock\Provider;

use Ekyna\Component\Commerce\Common\Context\Context;
use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Stock\Model\WarehouseInterface;

/**
 * Interface WarehouseProviderInterface
 * @package Ekyna\Component\Commerce\Stock\Provider
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface WarehouseProviderInterface
{
    /**
     * Returns the warehouse.
     *
     * @param Context|SaleInterface|null $context
     *
     * @return WarehouseInterface
     */
    public function getWarehouse($context = null): WarehouseInterface;
}
