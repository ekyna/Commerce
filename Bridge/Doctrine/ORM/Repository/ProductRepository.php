<?php

namespace Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Repository;

use Ekyna\Component\Commerce\Product\Repository\ProductRepositoryInterface;
use Ekyna\Component\Resource\Doctrine\ORM\TranslatableResourceRepository;

/**
 * Class ProductRepository
 * @package Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Repository
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ProductRepository extends TranslatableResourceRepository implements ProductRepositoryInterface
{
    /**
     * @inheritdoc
     */
    public function findOneById($id)
    {
        return $this->find($id);
    }
}
