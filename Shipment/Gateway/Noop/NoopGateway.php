<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Shipment\Gateway\Noop;

use Ekyna\Component\Commerce\Shipment\Gateway\AbstractGateway;

/**
 * Class NoopGateway
 * @package Ekyna\Component\Commerce\Shipment\Gateway\Noop
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class NoopGateway extends AbstractGateway
{
    public function getCapabilities(): int
    {
        return static::CAPABILITY_SHIPMENT | static::CAPABILITY_RETURN;
    }
}
