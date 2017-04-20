<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Shipment\Model;

use Doctrine\Common\Collections\Collection;
use Ekyna\Component\Commerce\Common\Model\MethodInterface;
use Ekyna\Component\Commerce\Pricing\Model\TaxableInterface;

/**
 * Interface ShipmentMethodInterface
 * @package Ekyna\Component\Commerce\Shipment\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface ShipmentMethodInterface extends MethodInterface, TaxableInterface
{
    public function getPlatformName(): ?string;

    /**
     * @return $this|ShipmentMethodInterface
     */
    public function setPlatformName(string $name): ShipmentMethodInterface;

    public function getGatewayName(): ?string;

    /**
     * @return $this|ShipmentMethodInterface
     */
    public function setGatewayName(string $name): ShipmentMethodInterface;

    public function getGatewayConfig(): array;

    /**
     * @return $this|ShipmentMethodInterface
     */
    public function setGatewayConfig(array $config): ShipmentMethodInterface;

    /**
     * @return Collection|ShipmentPriceInterface[]
     */
    public function getPrices(): Collection;

    public function hasPrices(): bool;

    public function hasPrice(ShipmentPriceInterface $price): bool;

    /**
     * @return $this|ShipmentMethodInterface
     */
    public function addPrice(ShipmentPriceInterface $price): ShipmentMethodInterface;

    /**
     * @return $this|ShipmentMethodInterface
     */
    public function removePrice(ShipmentPriceInterface $price): ShipmentMethodInterface;
}
