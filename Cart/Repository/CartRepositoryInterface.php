<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Cart\Repository;

use Ekyna\Component\Commerce\Cart\Model\CartInterface;
use Ekyna\Component\Commerce\Common\Repository\SaleRepositoryInterface;
use Ekyna\Component\Commerce\Customer\Model\CustomerInterface;

/**
 * Interface CartRepositoryInterface
 * @package Ekyna\Component\Commerce\Cart\Repository
 * @author  Etienne Dauvergne <contact@ekyna.com>
 *
 * @method CartInterface|null findOneById(int $id)
 * @method CartInterface|null findOneByKey(string $key)
 * @method CartInterface|null findOneByNumber(string $number)
 */
interface CartRepositoryInterface extends SaleRepositoryInterface
{
    /**
     * Finds the latest non expired customer cart.
     *
     * @param CustomerInterface $customer
     *
     * @return CartInterface|null
     */
    public function findLatestByCustomer(CustomerInterface $customer): ?CartInterface;

    /**
     * Finds the expired carts.
     *
     * @return CartInterface[]
     */
    public function findExpired(): array;
}
