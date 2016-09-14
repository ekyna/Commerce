<?php

namespace Ekyna\Component\Commerce\Customer\Repository;

use Ekyna\Component\Commerce\Customer\Model\CustomerGroupInterface;

/**
 * Interface CustomerGroupRepositoryInterface
 * @package Ekyna\Component\Commerce\Customer\Repository
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface CustomerGroupRepositoryInterface
{
    /**
     * Returns the default customer group.
     *
     * @return CustomerGroupInterface
     */
    public function findDefault();
}
