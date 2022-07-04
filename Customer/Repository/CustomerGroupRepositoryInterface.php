<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Customer\Repository;

use Ekyna\Component\Commerce\Customer\Model\CustomerGroupInterface;
use Ekyna\Component\Resource\Repository\ResourceRepositoryInterface;
use Ekyna\Component\Resource\Repository\TranslatableRepositoryInterface;

/**
 * Interface CustomerGroupRepositoryInterface
 * @package Ekyna\Component\Commerce\Customer\Repository
 * @author  Etienne Dauvergne <contact@ekyna.com>
 *
 * @implements ResourceRepositoryInterface<CustomerGroupInterface>
 */
interface CustomerGroupRepositoryInterface extends TranslatableRepositoryInterface
{
    /**
     * Returns the default customer group.
     */
    public function findDefault(): CustomerGroupInterface;

    /**
     * Returns the customer group identifiers.
     *
     * @return array<int>
     */
    public function getIdentifiers(): array;
}
