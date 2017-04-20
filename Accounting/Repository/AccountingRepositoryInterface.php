<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Accounting\Repository;

use Ekyna\Component\Commerce\Accounting\Model\AccountingInterface;
use Ekyna\Component\Resource\Repository\ResourceRepositoryInterface;

/**
 * Class AccountingRepositoryInterface
 * @package Ekyna\Component\Commerce\Accounting\Repository
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface AccountingRepositoryInterface extends ResourceRepositoryInterface
{
    /**
     * Finds accounting by types.
     *
     * @param array $types
     *
     * @return AccountingInterface[]
     */
    public function findByTypes(array $types): array;
}
