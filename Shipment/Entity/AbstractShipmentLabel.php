<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Shipment\Entity;

use DateTimeInterface;
use Ekyna\Component\Commerce\Exception\UnexpectedTypeException;
use Ekyna\Component\Commerce\Shipment\Model;

use function is_null;
use function is_resource;
use function is_string;

/**
 * Class AbstractShipmentLabel
 * @package Ekyna\Component\Commerce\Shipment\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
abstract class AbstractShipmentLabel implements Model\ShipmentLabelInterface
{
    protected ?int                           $id       = null;
    protected ?Model\ShipmentInterface       $shipment = null;
    protected ?Model\ShipmentParcelInterface $parcel   = null;
    /** @var resource|null */
    protected                    $content   = null;
    protected ?string            $type      = null;
    protected ?string            $format    = null;
    protected ?string            $size      = null;
    protected ?DateTimeInterface $updatedAt = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getShipment(): ?Model\ShipmentInterface
    {
        return $this->shipment;
    }

    public function setShipment(?Model\ShipmentInterface $shipment): Model\ShipmentLabelInterface
    {
        if ($this->shipment === $shipment) {
            return $this;
        }

        if ($previous = $this->shipment) {
            $this->shipment = null;
            $previous->removeLabel($this);
        }

        if ($this->shipment = $shipment) {
            $this->shipment->addLabel($this);
        }

        return $this;
    }

    public function getParcel(): ?Model\ShipmentParcelInterface
    {
        return $this->parcel;
    }

    public function setParcel(?Model\ShipmentParcelInterface $parcel): Model\ShipmentLabelInterface
    {
        if ($this->parcel === $parcel) {
            return $this;
        }

        if ($previous = $this->parcel) {
            $this->parcel = null;
            $previous->removeLabel($this);
        }

        if ($this->parcel = $parcel) {
            $this->parcel->addLabel($this);
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @inheritDoc
     */
    public function setContent($content): Model\ShipmentLabelInterface
    {
        if (!is_null($content) && !is_resource($content) && !is_string($content)) {
            throw new UnexpectedTypeException($content, ['resource', 'string', 'null']);
        }

        $this->content = $content;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): Model\ShipmentLabelInterface
    {
        $this->type = $type;

        return $this;
    }

    public function getFormat(): ?string
    {
        return $this->format;
    }

    public function setFormat(?string $format): Model\ShipmentLabelInterface
    {
        $this->format = $format;

        return $this;
    }

    public function getSize(): ?string
    {
        return $this->size;
    }

    public function setSize(?string $size): Model\ShipmentLabelInterface
    {
        $this->size = $size;

        return $this;
    }

    public function getUpdatedAt(): ?DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?DateTimeInterface $date): Model\ShipmentLabelInterface
    {
        $this->updatedAt = $date;

        return $this;
    }

    /**
     * Returns the available types.
     *
     * @return array<string>
     */
    public static function getTypes(): array
    {
        return [
            static::TYPE_SHIPMENT,
            static::TYPE_RETURN,
            static::TYPE_PROOF,
            static::TYPE_SUMMARY,
        ];
    }

    /**
     * Returns the available formats.
     *
     * @return array<string>
     */
    public static function getFormats(): array
    {
        return [
            static::FORMAT_GIF,
            static::FORMAT_JPEG,
            static::FORMAT_PNG,
        ];
    }

    /**
     * Returns the available sizes.
     *
     * @return array<string>
     */
    public static function getSizes(): array
    {
        return [
            static::SIZE_A6,
            static::SIZE_A5,
        ];
    }
}
