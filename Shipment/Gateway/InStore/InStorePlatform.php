<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Shipment\Gateway\InStore;

use Ekyna\Component\Commerce\Shipment\Gateway\AbstractPlatform;
use Ekyna\Component\Commerce\Shipment\Gateway\GatewayInterface;

/**
 * Class InStorePlatform
 * @package Ekyna\Component\Commerce\Shipment\Gateway\InStore
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class InStorePlatform extends AbstractPlatform
{
    public const NAME = 'in_store';


    public function getName(): string
    {
        return static::NAME;
    }

    public function createGateway(string $name, array $config = []): GatewayInterface
    {
        return new InStoreGateway($this, $name, $this->processGatewayConfig($config));
    }

    public function getActions(): array
    {
        return [];
    }
}
