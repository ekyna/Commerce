<?php

namespace Ekyna\Component\Commerce\Common\Adapter;

/**
 * Interface PersistenceAwareInterface
 * @package Ekyna\Component\Commerce\Common\Adapter
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface PersistenceAwareInterface
{
    /**
     * Sets the persistence adapter.
     *
     * @param PersistenceAdapterInterface $adapter
     */
    public function setPersistenceAdapter(PersistenceAdapterInterface $adapter);
}
