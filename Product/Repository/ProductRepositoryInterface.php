<?php

namespace Ekyna\Component\Commerce\Product\Repository;

use Ekyna\Component\Commerce\Product\Model\ProductInterface;
use Ekyna\Component\Resource\Doctrine\ORM\ResourceRepositoryInterface;

/**
 * Interface ProductRepositoryInterface
 * @package Ekyna\Component\Commerce\Product\Repository
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface ProductRepositoryInterface extends ResourceRepositoryInterface
{
    /**
     * Finds the product by id.
     *
     * @param int $id
     *
     * @return ProductInterface|null
     */
    public function findOneById($id);
}
