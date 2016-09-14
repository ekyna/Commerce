<?php

namespace Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Repository;

use Ekyna\Component\Commerce\Customer\Repository\CustomerGroupRepositoryInterface;
use Ekyna\Component\Commerce\Exception\RuntimeException;
use Ekyna\Component\Resource\Doctrine\ORM\ResourceRepository;

/**
 * Class CustomerGroupRepository
 * @package Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Repository
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CustomerGroupRepository extends ResourceRepository implements CustomerGroupRepositoryInterface
{
    /**
     * @inheritDoc
     */
    public function findDefault()
    {
        if (null !== $defaultGroup = $this->findOneBy(['default' => true])) {
            return $defaultGroup;
        }

        throw new RuntimeException('Default customer group not found.');
    }
}
