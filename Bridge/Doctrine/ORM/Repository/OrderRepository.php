<?php

namespace Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Repository;

use Ekyna\Component\Commerce\Order\Repository\OrderRepositoryInterface;

/**
 * Class OrderRepository
 * @package Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Repository
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class OrderRepository extends AbstractSaleRepository implements OrderRepositoryInterface
{
    /**
     * @inheritdoc
     */
    protected function getAlias()
    {
        return 'o';
    }
}
