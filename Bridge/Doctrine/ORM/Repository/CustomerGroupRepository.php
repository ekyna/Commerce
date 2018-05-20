<?php

namespace Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Repository;

use Doctrine\ORM\Event\OnClearEventArgs;
use Ekyna\Component\Commerce\Customer\Model\CustomerGroupInterface;
use Ekyna\Component\Commerce\Customer\Repository\CustomerGroupRepositoryInterface;
use Ekyna\Component\Commerce\Exception\RuntimeException;
use Ekyna\Component\Resource\Doctrine\ORM\TranslatableResourceRepository;

/**
 * Class CustomerGroupRepository
 * @package Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Repository
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CustomerGroupRepository extends TranslatableResourceRepository implements CustomerGroupRepositoryInterface
{
    /**
     * @var CustomerGroupInterface
     */
    private $defaultGroup;


    /**
     * @inheritDoc
     */
    public function findDefault()
    {
        if (null !== $this->defaultGroup) {
            return $this->defaultGroup;
        }

        if (null !== $this->defaultGroup = $this->findOneBy(['default' => true])) {
            return $this->defaultGroup;
        }

        throw new RuntimeException('Default customer group not found.');
    }

    /**
     * On clear event handler.
     *
     * @param OnClearEventArgs $event
     */
    public function onClear(OnClearEventArgs $event)
    {
        if ((null === $event->getEntityClass()) || ($this->getClassName() === $event->getEntityClass())) {
            $this->defaultGroup = null;
        }
    }
}
