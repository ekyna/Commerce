<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Common\Repository;

use Ekyna\Component\Commerce\Common\Model\CouponInterface;
use Ekyna\Component\Commerce\Customer\Model\CustomerInterface;
use Ekyna\Component\Resource\Repository\ResourceRepositoryInterface;

/**
 * Interface CouponRepositoryInterface
 * @package Ekyna\Component\Commerce\Common\Repository
 * @author  Etienne Dauvergne <contact@ekyna.com>
 *
 * @implements ResourceRepositoryInterface<CouponInterface>
 */
interface CouponRepositoryInterface extends ResourceRepositoryInterface
{
    /**
     * Finds the coupon by its code.
     *
     * @param string $code
     *
     * @return CouponInterface|null
     */
    public function findOneByCode(string $code): ?CouponInterface;

    /**
     * Finds coupons by customer.
     *
     * @param CustomerInterface $customer
     * @param bool              $unused
     *
     * @return CouponInterface[]
     */
    public function findByCustomer(CustomerInterface $customer, bool $unused = true): array;
}
