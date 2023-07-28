<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Shipment\Gateway\Virtual;

use Ekyna\Component\Commerce\Shipment\Gateway\AbstractPlatform;
use Ekyna\Component\Commerce\Shipment\Gateway\GatewayInterface;

/**
 * Class VirtualPlatform
 * @package Ekyna\Component\Commerce\Shipment\Gateway\Virtual
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class VirtualPlatform extends AbstractPlatform
{
    public const NAME = 'virtual';

    public function getName(): string
    {
        return static::NAME;
    }

    public function createGateway(string $name, array $config = []): GatewayInterface
    {
        return new VirtualGateway($this, $name, $this->processGatewayConfig($config));
    }

    public function getActions(): array
    {
        return [];
    }
}
