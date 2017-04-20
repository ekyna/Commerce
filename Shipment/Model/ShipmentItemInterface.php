<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Shipment\Model;

use Decimal\Decimal;
use Doctrine\Common\Collections\Collection;
use Ekyna\Component\Commerce\Common\Model\SaleItemInterface;
use Ekyna\Component\Resource\Model\ResourceInterface;

/**
 * Interface ShipmentItemInterface
 * @package Ekyna\Component\Commerce\Shipment\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface ShipmentItemInterface extends ResourceInterface
{
    public function getShipment(): ?ShipmentInterface;

    public function setShipment(?ShipmentInterface $shipment): ShipmentItemInterface;

    public function getSaleItem(): ?SaleItemInterface;

    public function setSaleItem(SaleItemInterface $saleItem): ShipmentItemInterface;

    public function getQuantity(): Decimal;

    public function setQuantity(Decimal $quantity): ShipmentItemInterface;

    /**
     * @param array<static> $children
     */
    public function setChildren(array $children): ShipmentItemInterface;

    /**
     * @return Collection|array<static>
     */
    public function getChildren();

    public function clearChildren(): ShipmentItemInterface;

    public function getExpected(): ?Decimal;

    public function setExpected(?Decimal $expected): ShipmentItemInterface;

    public function getAvailable(): ?Decimal;

    public function setAvailable(?Decimal $available): ShipmentItemInterface;
}
