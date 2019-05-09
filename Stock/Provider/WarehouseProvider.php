<?php

namespace Ekyna\Component\Commerce\Stock\Provider;

use Ekyna\Component\Commerce\Common\Context\Context;
use Ekyna\Component\Commerce\Common\Context\ContextProviderInterface;
use Ekyna\Component\Commerce\Common\Model\SaleInterface;
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
     * @var ContextProviderInterface
     */
    protected $contextProvider;

    /**
     * @var WarehouseRepositoryInterface
     */
    protected $warehouseRepository;


    /**
     * Constructor.
     *
     * @param ContextProviderInterface     $contextProvider
     * @param WarehouseRepositoryInterface $warehouseRepository
     */
    public function __construct(
        ContextProviderInterface $contextProvider,
        WarehouseRepositoryInterface $warehouseRepository
    ) {
        $this->contextProvider = $contextProvider;
        $this->warehouseRepository = $warehouseRepository;
    }

    /**
     * @inheritDoc
     */
    public function getWarehouse($context = null): WarehouseInterface
    {
        if ($context instanceof SaleInterface) {
            $context = $this->contextProvider->getContext($context);
        }

        if (!$context instanceof Context) {
            $context = $this->contextProvider->getContext();
        }

        $warehouse = $this
            ->warehouseRepository
            ->findOneByCountry($context->getDeliveryCountry());

        if (null !== $warehouse) {
            return $warehouse;
        }

        return $this->warehouseRepository->findDefault();
    }
}
