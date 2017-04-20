<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Shipment\Gateway\Noop;

use Ekyna\Component\Commerce\Shipment\Gateway\AbstractPlatform;
use Ekyna\Component\Commerce\Shipment\Gateway\GatewayInterface;

/**
 * Class NullPlatform
 * @package Ekyna\Component\Commerce\Shipment\Gateway\Noop
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class NoopPlatform extends AbstractPlatform
{
    public const NAME = 'noop';


    public function getName(): string
    {
        return static::NAME;
    }

    public function createGateway(string $name, array $config = []): GatewayInterface
    {
        return new NoopGateway($this, $name, $this->processGatewayConfig($config));
    }

    public function getActions(): array
    {
        return [];
    }
}
