<?php

namespace Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Repository;

use Ekyna\Component\Commerce\Product\Model\ProductInterface;
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

    /**
     * @inheritdoc
     */
    public function findParentsByBundled(ProductInterface $bundled)
    {
        $qb = $this->getQueryBuilder();

        return $qb
            ->join('o.bundleSlots', 'slot')
            ->join('slot.choices', 'choice')
            ->andWhere($qb->expr()->eq('choice.product', ':bundled'))
            ->setParameter('bundled', $bundled)
            ->getQuery()
            ->getResult();
    }
}
