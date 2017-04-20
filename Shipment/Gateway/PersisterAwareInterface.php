<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Shipment\Gateway;

/**
 * Interface PersisterAwareInterface
 * @package Ekyna\Component\Commerce\Shipment\Gateway
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface PersisterAwareInterface
{
    public function setPersister(PersisterInterface $persister): void;

    public function getPersister(): PersisterInterface;
}
