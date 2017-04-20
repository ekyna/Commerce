<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Shipment\Model;

use DateTimeInterface;

/**
 * Interface ShipmentLabelInterface
 * @package Ekyna\Component\Commerce\Shipment\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface ShipmentLabelInterface
{
    public const TYPE_SHIPMENT = 'shipment';
    public const TYPE_RETURN   = 'return';
    public const TYPE_PROOF    = 'proof';
    public const TYPE_SUMMARY  = 'summary';
    public const TYPE_CUSTOMS  = 'customs';

    public const FORMAT_PNG  = 'image/png';
    public const FORMAT_GIF  = 'image/gif';
    public const FORMAT_JPEG = 'image/jpeg';
    public const FORMAT_PDF  = 'application/pdf';

    public const SIZE_A6 = 'a6';
    public const SIZE_A5 = 'a5';
    public const SIZE_A4 = 'a4';

    public function getId(): ?int;

    public function getShipment(): ?ShipmentInterface;

    public function setShipment(?ShipmentInterface $shipment): ShipmentLabelInterface;

    public function getParcel(): ?ShipmentParcelInterface;

    public function setParcel(?ShipmentParcelInterface $parcel);

    public function getContent(): ?string;

    public function setContent(string $content): ShipmentLabelInterface;

    public function getType(): ?string;

    public function setType(?string $type): ShipmentLabelInterface;

    public function getFormat(): ?string;

    public function setFormat(?string $format): ShipmentLabelInterface;

    public function getSize(): ?string;

    public function setSize(?string $size): ShipmentLabelInterface;

    public function getUpdatedAt(): ?DateTimeInterface;

    public function setUpdatedAt(?DateTimeInterface $date): ShipmentLabelInterface;
}
