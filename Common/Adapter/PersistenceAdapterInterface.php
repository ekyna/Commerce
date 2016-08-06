<?php

namespace Ekyna\Component\Commerce\Common\Adapter;

/**
 * Interface PersistenceAdapterInterface
 * @package Ekyna\Component\Commerce\Common\Adapter
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface PersistenceAdapterInterface
{
    /**
     * Persists the entity.
     *
     * @param object $entity
     */
    public function persist($entity);

    /**
     * Removes the entity.
     *
     * @param object $entity
     */
    public function remove($entity);
}
