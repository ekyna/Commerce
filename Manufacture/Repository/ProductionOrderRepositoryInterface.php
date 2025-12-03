<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Manufacture\Repository;

use Ekyna\Component\Commerce\Manufacture\Model\ProductionOrderInterface;
use Ekyna\Component\Resource\Repository\ResourceRepositoryInterface;

/**
 * Interface ProductionOrderRepositoryInterface
 * @package Ekyna\Component\Commerce\Manufacture\Repository
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface ProductionOrderRepositoryInterface extends ResourceRepositoryInterface
{
    /**
     * @return array<int, ProductionOrderInterface>
     */
    public function findScheduled(): array;
}
