<?php

namespace Ekyna\Component\Commerce\Shipment\Gateway;

/**
 * Trait PersisterAwareTrait
 * @package Ekyna\Component\Commerce\Shipment\Gateway
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
trait PersisterAwareTrait
{
    /**
     * @var PersisterInterface
     */
    protected $persister;


    /**
     * Sets the persister.
     *
     * @param PersisterInterface $persister
     */
    public function setPersister(PersisterInterface $persister)
    {
        $this->persister = $persister;
    }

    /**
     * Returns the persister.
     *
     * @return PersisterInterface
     */
    public function getPersister()
    {
        return $this->persister;
    }
}
