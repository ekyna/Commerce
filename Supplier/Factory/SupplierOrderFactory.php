<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Supplier\Factory;

use Ekyna\Component\Commerce\Stock\Repository\WarehouseRepositoryInterface;
use Ekyna\Component\Commerce\Supplier\Model\SupplierInterface;
use Ekyna\Component\Commerce\Supplier\Model\SupplierOrderInterface;
use Ekyna\Component\Commerce\Supplier\Updater\SupplierOrderUpdaterInterface;
use Ekyna\Component\Resource\Doctrine\ORM\Factory\ResourceFactory;
use Ekyna\Component\Resource\Model\ResourceInterface;

/**
 * Class SupplierOrderFactory
 * @package Ekyna\Component\Commerce\Supplier\Factory
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class SupplierOrderFactory extends ResourceFactory implements SupplierOrderFactoryInterface
{
    protected WarehouseRepositoryInterface  $warehouseRepository;
    protected SupplierOrderUpdaterInterface $orderUpdater;

    public function __construct(
        WarehouseRepositoryInterface  $warehouseRepository,
        SupplierOrderUpdaterInterface $orderUpdater
    ) {
        $this->warehouseRepository = $warehouseRepository;
        $this->orderUpdater = $orderUpdater;
    }

    /**
     * @return SupplierOrderInterface
     */
    public function create(): ResourceInterface
    {
        /** @var SupplierOrderInterface $order */
        $order = parent::create();

        $order->setWarehouse($this->warehouseRepository->findDefault());

        return $order;
    }

    public function createWithSupplier(SupplierInterface $supplier): SupplierOrderInterface
    {
        /** @var SupplierOrderInterface $order */
        $order = $this->create();

        $order->setSupplier($supplier);

        $this->orderUpdater->updateCurrency($order);

        return $order;
    }
}
