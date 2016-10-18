<?php

namespace Ekyna\Component\Commerce\Product\Repository;

use Ekyna\Component\Commerce\Product\Model\ProductInterface;
use Ekyna\Component\Resource\Doctrine\ORM\TranslatableResourceRepositoryInterface;

/**
 * Interface ProductRepositoryInterface
 * @package Ekyna\Component\Commerce\Product\Repository
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface ProductRepositoryInterface extends TranslatableResourceRepositoryInterface
{
    /**
     * Finds the product by id.
     *
     * @param int $id
     *
     * @return ProductInterface|null
     */
    public function findOneById($id);

    /**
     * Finds the parents products of the given bundled product.
     *
     * @param ProductInterface $bundled
     *
     * @return array|ProductInterface[]
     */
    public function findParentsByBundled(ProductInterface $bundled);
}
