<?php

namespace Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Repository;

use Doctrine\ORM\EntityRepository;
use Ekyna\Component\Commerce\Product\Repository\ProductRepositoryInterface;

/**
 * Class ProductRepository
 * @package Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Repository
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ProductRepository extends EntityRepository implements ProductRepositoryInterface
{
    /**
     * @inheritdoc
     */
    public function findById($id)
    {
        return $this->find($id);
    }
}
