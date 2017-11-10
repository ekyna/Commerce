<?php

namespace Ekyna\Component\Commerce\Cart\Repository;

use Ekyna\Component\Commerce\Cart\Model\CartInterface;
use Ekyna\Component\Commerce\Common\Repository\SaleRepositoryInterface;
use Ekyna\Component\Commerce\Customer\Model\CustomerInterface;

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

    /**
     * Finds the latest non expired customer cart.
     *
     * @param CustomerInterface $customer
     *
     * @return CartInterface|null
     */
    public function findLatestByCustomer(CustomerInterface $customer);

    /**
     * Finds the expired carts.
     *
     * @return CartInterface[]
     */
    public function findExpired();
}
