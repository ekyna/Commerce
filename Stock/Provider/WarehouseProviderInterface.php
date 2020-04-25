<?php

namespace Ekyna\Component\Commerce\Stock\Provider;

use Ekyna\Component\Commerce\Common\Model\CountryInterface;
use Ekyna\Component\Commerce\Stock\Model\WarehouseInterface;

/**
 * Interface WarehouseProviderInterface
 * @package Ekyna\Component\Commerce\Stock\Provider
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface WarehouseProviderInterface
{
    /**
     * Returns the warehouse for the given country.
     *
     * @param CountryInterface|null $country
     *
     * @return WarehouseInterface
     */
    public function getWarehouse(CountryInterface $country = null): WarehouseInterface;
}
