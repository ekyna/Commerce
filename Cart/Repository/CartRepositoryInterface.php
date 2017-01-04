<?php

namespace Ekyna\Component\Commerce\Cart\Repository;

use Ekyna\Component\Commerce\Cart\Model\CartInterface;
use Ekyna\Component\Commerce\Common\Repository\SaleRepositoryInterface;

/**
 * Interface CartRepositoryInterface
 * @package Ekyna\Component\Commerce\Cart\Repository
 * @author  Etienne Dauvergne <contact@ekyna.com>
 *
 * @method CartInterface|null findOneById($id)
 * @method CartInterface|null findOneByKey($key)
 */
interface CartRepositoryInterface extends SaleRepositoryInterface
{
    /**
     * Creates a new cart instance.
     *
     * @return CartInterface
     */
    public function createNew();
}
