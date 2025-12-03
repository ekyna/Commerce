<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Repository;

use Ekyna\Component\Commerce\Manufacture\Model\POState;
use Ekyna\Component\Commerce\Manufacture\Repository\ProductionOrderRepositoryInterface;
use Ekyna\Component\Resource\Doctrine\ORM\Repository\ResourceRepository;

/**
 * Class ProductionOrderRepository
 * @package Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Repository
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ProductionOrderRepository extends ResourceRepository implements ProductionOrderRepositoryInterface
{
    public function findScheduled(): array
    {
        return $this->findBy(['state' => POState::SCHEDULED], ['startAt' => 'ASC']);
    }
}
