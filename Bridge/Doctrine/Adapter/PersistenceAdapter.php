<?php

namespace Ekyna\Component\Commerce\Bridge\Doctrine\Adapter;

use Doctrine\Common\Persistence\ManagerRegistry;
use Ekyna\Component\Commerce\Common\Adapter\PersistenceAdapterInterface;

/**
 * Class PersistenceAdapter
 * @package Ekyna\Component\Commerce\Bridge\Doctrine\Adapter
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class PersistenceAdapter implements PersistenceAdapterInterface
{
    /**
     * @var ManagerRegistry
     */
    private $registry;


    /**
     * Constructor.
     *
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        $this->registry = $registry;
    }

    /**
     * @inheritdoc
     */
    public function persist($entity)
    {
        // TODO check if recompute is needed

        $this->getManager($entity)->persist($entity);
    }

    /**
     * @inheritdoc
     */
    public function remove($entity)
    {
        $this->getManager($entity)->remove($entity);
    }

    /**
     * Returns the manager for the given entity.
     *
     * @param object $entity
     *
     * @return \Doctrine\Common\Persistence\ObjectManager
     */
    private function getManager($entity)
    {
        return $this->registry->getManagerForClass(get_class($entity));
    }
}
