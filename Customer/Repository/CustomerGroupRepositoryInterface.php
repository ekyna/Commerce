<?php

namespace Ekyna\Component\Commerce\Customer\Repository;

use Ekyna\Component\Commerce\Customer\Model\CustomerGroupInterface;
use Ekyna\Component\Resource\Doctrine\ORM\TranslatableResourceRepositoryInterface;

/**
 * Interface CustomerGroupRepositoryInterface
 * @package Ekyna\Component\Commerce\Customer\Repository
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface CustomerGroupRepositoryInterface extends TranslatableResourceRepositoryInterface
{
    /**
     * Returns the default customer group.
     *
     * @return CustomerGroupInterface
     */
    public function findDefault();
}
