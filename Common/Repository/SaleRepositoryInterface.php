<?php

namespace Ekyna\Component\Commerce\Common\Repository;

use Ekyna\Component\Commerce\Common\Model\SaleInterface;

/**
 * Interface SaleRepositoryInterface
 * @package Ekyna\Component\Commerce\Common\Repository
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface SaleRepositoryInterface
{
    /**
     * Finds the sale by its id.
     *
     * @param int $id
     *
     * @return SaleInterface|null
     */
    public function findOneById($id);
}
