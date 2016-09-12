<?php

namespace Ekyna\Component\Commerce\Cart\Repository;

use Ekyna\Component\Commerce\Cart\Model\CartInterface;
use Ekyna\Component\Resource\Doctrine\ORM\ResourceRepositoryInterface;

/**
 * Interface CartRepositoryInterface
 * @package Ekyna\Component\Commerce\Cart\Repository
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface CartRepositoryInterface extends ResourceRepositoryInterface
{
    /**
     * Returns the cart by id.
     *
     * @param int $id
     *
     * @return CartInterface|null
     */
    public function findOneById($id);
}
