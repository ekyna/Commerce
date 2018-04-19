<?php

namespace Ekyna\Component\Commerce\Shipment\Gateway;

/**
 * Interface PersisterAwareInterface
 * @package Ekyna\Component\Commerce\Shipment\Gateway
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface PersisterAwareInterface
{
    /**
     * Sets the persister.
     *
     * @param PersisterInterface $persister
     */
    public function setPersister(PersisterInterface $persister);

    /**
     * Returns the shipment persister.
     *
     * @return PersisterInterface
     */
    public function getPersister();
}
