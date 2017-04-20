<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Provider;

use Ekyna\Component\Commerce\Shipment\Gateway\AbstractProvider;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentMethodInterface;
use Ekyna\Component\Commerce\Shipment\Repository\ShipmentMethodRepositoryInterface;

/**
 * Class ShipmentGatewayProvider
 * @package Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Provider
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ShipmentGatewayProvider extends AbstractProvider
{
    private ShipmentMethodRepositoryInterface $shipmentMethodRepository;

    public function __construct(ShipmentMethodRepositoryInterface $shipmentMethodRepository)
    {
        $this->shipmentMethodRepository = $shipmentMethodRepository;
    }

    protected function loadGateways(): void
    {
        $methods = $this->shipmentMethodRepository->findAll();

        /** @var ShipmentMethodInterface $method */
        foreach ($methods as $method) {
            $this->createAndRegisterGateway(
                $method->getPlatformName(),
                $method->getGatewayName(),
                $method->getGatewayConfig()
            );
        }
    }
}
