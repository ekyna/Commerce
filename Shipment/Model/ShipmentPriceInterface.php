<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Shipment\Model;

use Decimal\Decimal;
use Ekyna\Component\Resource\Model\ResourceInterface;

/**
 * Class ShipmentPrice
 * @package Ekyna\Component\Commerce\Shipment\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface ShipmentPriceInterface extends ResourceInterface
{
    public function getZone(): ?ShipmentZoneInterface;

    public function setZone(?ShipmentZoneInterface $zone): ShipmentPriceInterface;

    public function getMethod(): ?ShipmentMethodInterface;

    public function setMethod(?ShipmentMethodInterface $method): ShipmentPriceInterface;

    /**
     * Returns the weight (kilograms).
     */
    public function getWeight(): Decimal;

    /**
     * Sets the weight (kilograms).
     */
    public function setWeight(Decimal $weight): ShipmentPriceInterface;

    public function getNetPrice(): Decimal;

    public function setNetPrice(Decimal $price): ShipmentPriceInterface;
}
