<?php

namespace Ekyna\Component\Commerce\Accounting\Repository;

use Ekyna\Component\Resource\Doctrine\ORM\ResourceRepositoryInterface;

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
     * @return mixed
     */
    public function findByTypes(array $types);
}