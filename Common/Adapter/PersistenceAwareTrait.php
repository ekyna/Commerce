<?php

namespace Ekyna\Component\Commerce\Common\Adapter;

use Ekyna\Component\Commerce\Exception\RuntimeException;

/**
 * Trait PersistenceAwareTrait
 * @package Ekyna\Component\Commerce\Common\Adapter
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
trait PersistenceAwareTrait
{
    /**
     * @var PersistenceAdapterInterface
     */
    protected $persistenceAdapter;


    /**
     * Sets the persistence adapter.
     *
     * @param PersistenceAdapterInterface $adapter
     */
    public function setPersistenceAdapter(PersistenceAdapterInterface $adapter)
    {
        $this->persistenceAdapter = $adapter;
    }

    /**
     * Persists the entity.
     *
     * @param object $entity
     *
     * @return $this|PersistenceAwareTrait
     */
    protected function persist($entity)
    {
        $this->assertPersistenceAdataper();

        $this->persistenceAdapter->persist($entity);

        return $this;
    }

    /**
     * Removes the entity.
     *
     * @param object $entity
     *
     * @return $this|PersistenceAwareTrait
     */
    protected function remove($entity)
    {
        $this->assertPersistenceAdataper();

        $this->persistenceAdapter->remove($entity);

        return $this;
    }

    /**
     * Asserts that the persistence adapter is set.
     *
     * @throws RuntimeException
     */
    private function assertPersistenceAdataper()
    {
        if (null === $this->persistenceAdapter) {
            throw new RuntimeException('Persistence adapter must be defined.');
        }
    }
}
