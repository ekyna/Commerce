<?php

namespace Ekyna\Component\Commerce\Shipment\Model;

/**
 * Interface ShipmentLabelInterface
 * @package Ekyna\Component\Commerce\Shipment\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface ShipmentLabelInterface
{
    const TYPE_SHIPMENT = 'shipment';
    const TYPE_RETURN   = 'return';
    const TYPE_PROOF    = 'proof';
    const TYPE_SUMMARY  = 'summary';
    const TYPE_CUSTOMS  = 'customs';

    const FORMAT_PNG  = 'image/png';
    const FORMAT_GIF  = 'image/gif';
    const FORMAT_JPEG = 'image/jpeg';
    const FORMAT_PDF  = 'application/pdf';

    const SIZE_A6 = 'a6';
    const SIZE_A5 = 'a5';
    const SIZE_A4 = 'a4';


    /**
     * Returns the id.
     *
     * @return int
     */
    public function getId();

    /**
     * Returns the shipment.
     *
     * @return ShipmentInterface
     */
    public function getShipment();

    /**
     * Sets the shipment.
     *
     * @param ShipmentInterface $shipment
     *
     * @return $this|ShipmentParcelInterface
     */
    public function setShipment(ShipmentInterface $shipment = null);

    /**
     * Returns the parcel.
     *
     * @return ShipmentInterface
     */
    public function getParcel();

    /**
     * Sets the parcel.
     *
     * @param ShipmentParcelInterface $parcel
     *
     * @return $this|ShipmentParcelInterface
     */
    public function setParcel(ShipmentParcelInterface $parcel = null);

    /**
     * Returns the content.
     *
     * @return string|resource
     */
    public function getContent();

    /**
     * Sets the content.
     *
     * @param string $content
     *
     * @return $this|ShipmentLabelInterface
     */
    public function setContent(string $content);

    /**
     * Returns the type.
     *
     * @return string
     */
    public function getType();

    /**
     * Sets the type.
     *
     * @param string $type
     *
     * @return $this|ShipmentLabelInterface
     */
    public function setType($type);

    /**
     * Returns the format.
     *
     * @return string
     */
    public function getFormat();

    /**
     * Sets the format.
     *
     * @param string $format
     *
     * @return $this|ShipmentLabelInterface
     */
    public function setFormat(string $format);

    /**
     * Returns the size.
     *
     * @return string
     */
    public function getSize();

    /**
     * Sets the size.
     *
     * @param string $size
     *
     * @return $this|ShipmentLabelInterface
     */
    public function setSize(string $size);

    /**
     * Returns the "updated at" date time.
     *
     * @return \DateTime
     */
    public function getUpdatedAt();

    /**
     * Sets the "updated at" date time.
     *
     * @param \DateTime $date
     *
     * @return $this|ShipmentLabelInterface
     */
    public function setUpdatedAt(\DateTime $date = null);
}