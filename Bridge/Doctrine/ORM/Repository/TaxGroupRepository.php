<?php

namespace Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Repository;

use Ekyna\Component\Commerce\Exception\RuntimeException;
use Ekyna\Component\Commerce\Pricing\Repository\TaxGroupRepositoryInterface;
use Ekyna\Component\Resource\Doctrine\ORM\ResourceRepository;

/**
 * Class TaxGroupRepository
 * @package Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Repository
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class TaxGroupRepository extends ResourceRepository implements TaxGroupRepositoryInterface
{
    /**
     * @inheritdoc
     */
    public function findDefault()
    {
        if (null !== $taxGroup = $this->findOneBy(['default' => true])) {
            return $taxGroup;
        }

        throw new RuntimeException('Default tax group not found.');
    }
}
