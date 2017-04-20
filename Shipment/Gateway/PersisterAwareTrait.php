<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Shipment\Gateway;

/**
 * Trait PersisterAwareTrait
 * @package Ekyna\Component\Commerce\Shipment\Gateway
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
trait PersisterAwareTrait
{
    protected PersisterInterface $persister;

    public function setPersister(PersisterInterface $persister): void
    {
        $this->persister = $persister;
    }

    public function getPersister(): PersisterInterface
    {
        return $this->persister;
    }
}
