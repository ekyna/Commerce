<?php

namespace Ekyna\Component\Commerce\Shipment\Entity;

use Ekyna\Component\Commerce\Shipment\Model;

/**
 * Class AbstractShipmentLabel
 * @package Ekyna\Component\Commerce\Shipment\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
abstract class AbstractShipmentLabel implements Model\ShipmentLabelInterface
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var Model\ShipmentInterface
     */
    protected $shipment;

    /**
     * @var Model\ShipmentParcelInterface
     */
    protected $parcel;

    /**
     * @var string
     */
    protected $content;

    /**
     * @var string
     */
    protected $type;

    /**
     * @var string
     */
    protected $format;

    /**
     * @var string
     */
    protected $size;

    /**
     * @var \DateTime
     */
    protected $updatedAt;


    /**
     * @inheritdoc
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @inheritDoc
     */
    public function getShipment()
    {
        return $this->shipment;
    }

    /**
     * @inheritdoc
     */
    public function setShipment(Model\ShipmentInterface $shipment = null)
    {
        if ($this->shipment !== $shipment) {
            $previous = $this->shipment;
            $this->shipment = $shipment;

            if ($previous) {
                $previous->removeLabel($this);
            }

            if ($this->shipment) {
                $this->shipment->addLabel($this);
            }
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getParcel()
    {
        return $this->parcel;
    }

    /**
     * @inheritDoc
     */
    public function setParcel(Model\ShipmentParcelInterface $parcel = null)
    {
        if ($this->parcel !== $parcel) {
            $previous = $this->parcel;
            $this->parcel = $parcel;

            if ($previous) {
                $previous->removeLabel($this);
            }

            if ($this->parcel) {
                $this->parcel->addLabel($this);
            }
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @inheritdoc
     */
    public function setContent(string $content)
    {
        $this->content = $content;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @inheritdoc
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getFormat()
    {
        return $this->format;
    }

    /**
     * @inheritdoc
     */
    public function setFormat(string $format)
    {
        $this->format = $format;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * @inheritdoc
     */
    public function setSize(string $size)
    {
        $this->size = $size;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * @inheritdoc
     */
    public function setUpdatedAt(\DateTime $date = null)
    {
        $this->updatedAt = $date;

        return $this;
    }

    /**
     * Returns the available types.
     *
     * @return string[]
     */
    public static function getTypes()
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
     * @return string[]
     */
    public static function getFormats()
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
     * @return string[]
     */
    public static function getSizes()
    {
        return [
            static::SIZE_A6,
            static::SIZE_A5,
        ];
    }
}
