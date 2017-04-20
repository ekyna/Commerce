<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Repository;

use Doctrine\ORM\Event\OnClearEventArgs;
use Ekyna\Component\Commerce\Customer\Model\CustomerGroupInterface;
use Ekyna\Component\Commerce\Customer\Repository\CustomerGroupRepositoryInterface;
use Ekyna\Component\Commerce\Exception\RuntimeException;
use Ekyna\Component\Resource\Doctrine\ORM\Repository\TranslatableRepository;

/**
 * Class CustomerGroupRepository
 * @package Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Repository
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CustomerGroupRepository extends TranslatableRepository implements CustomerGroupRepositoryInterface
{
    private ?CustomerGroupInterface $defaultGroup = null;


    /**
     * @inheritDoc
     */
    public function findDefault(): CustomerGroupInterface
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
     * @inheritDoc
     */
    public function getIdentifiers(): array
    {
        $qb = $this->createQueryBuilder('g');

        $result = $qb
            ->select('g.id')
            ->orderBy('g.id')
            ->getQuery()
            ->getScalarResult();

        return array_map(function ($r) { return $r['id']; }, $result);
    }

    /**
     * On clear event handler.
     *
     * @param OnClearEventArgs $event
     */
    public function onClear(OnClearEventArgs $event): void
    {
        if ((null === $event->getEntityClass()) || ($this->getClassName() === $event->getEntityClass())) {
            $this->defaultGroup = null;
        }
    }
}
