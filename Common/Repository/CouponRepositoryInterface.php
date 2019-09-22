<?php

namespace Ekyna\Component\Commerce\Common\Repository;

use Ekyna\Component\Commerce\Common\Model\CouponInterface;
use Ekyna\Component\Resource\Doctrine\ORM\ResourceRepositoryInterface;

/**
 * Interface CouponRepositoryInterface
 * @package Ekyna\Component\Commerce\Common\Repository
 * @author  Etienne Dauvergne <contact@ekyna.com>
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
}