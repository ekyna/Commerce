<?php

namespace Ekyna\Component\Commerce\Order\Repository;

use Ekyna\Component\Commerce\Common\Repository\SaleRepositoryInterface;
use Ekyna\Component\Commerce\Order\Model\OrderInterface;

/**
 * Interface OrderRepositoryInterface
 * @package Ekyna\Component\Commerce\Order\Repository
 * @author  Etienne Dauvergne <contact@ekyna.com>
 *
 * @method OrderInterface|null findOneById($id)
 * @method OrderInterface|null findOneByKey($key)
 */
interface OrderRepositoryInterface extends SaleRepositoryInterface
{
    /**
     * Creates a new order instance.
     *
     * @return OrderInterface
     */
    public function createNew();
}
