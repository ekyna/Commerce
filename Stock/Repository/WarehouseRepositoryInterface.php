<?php

namespace Ekyna\Component\Commerce\Stock\Repository;

use Ekyna\Component\Commerce\Common\Model\CountryInterface;
use Ekyna\Component\Commerce\Stock\Model\WarehouseInterface;
use Ekyna\Component\Resource\Doctrine\ORM\ResourceRepositoryInterface;

/**
 * Interface WarehouseRepositoryInterface
 * @package Ekyna\Component\Commerce\Stock\Repository
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface WarehouseRepositoryInterface extends ResourceRepositoryInterface
{
    /**
     * Returns the default warehouse.
     *
     * @param bool $throwException Whether to throw exception if not found.
     *
     * @return WarehouseInterface
     */
    public function findDefault(bool $throwException = true): ?WarehouseInterface;

    /**
     * Returns the warehouse for the given country.
     *
     * @param CountryInterface $country
     *
     * @return WarehouseInterface|null
     */
    public function findOneByCountry(CountryInterface $country): ?WarehouseInterface;
}
