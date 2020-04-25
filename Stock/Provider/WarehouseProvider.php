<?php

namespace Ekyna\Component\Commerce\Stock\Provider;

use Ekyna\Component\Commerce\Common\Model\CountryInterface;
use Ekyna\Component\Commerce\Stock\Model\WarehouseInterface;
use Ekyna\Component\Commerce\Stock\Repository\WarehouseRepositoryInterface;

/**
 * Class WarehouseProvider
 * @package Ekyna\Component\Commerce\Stock\Provider
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class WarehouseProvider implements WarehouseProviderInterface
{
    /**
     * @var WarehouseRepositoryInterface
     */
    protected $warehouseRepository;


    /**
     * Constructor.
     *
     * @param WarehouseRepositoryInterface $warehouseRepository
     */
    public function __construct(WarehouseRepositoryInterface $warehouseRepository)
    {
        $this->warehouseRepository = $warehouseRepository;
    }

    /**
     * @inheritDoc
     */
    public function getWarehouse(CountryInterface $country = null): WarehouseInterface
    {
        if ($country) {
            $warehouse = $this->warehouseRepository->findOneByCountry($country);

            if ($warehouse) {
                return $warehouse;
            }
        }

        return $this->warehouseRepository->findDefault();
    }
}
