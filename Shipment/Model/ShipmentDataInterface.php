<?php

namespace Ekyna\Component\Commerce\Shipment\Model;

use Doctrine\Common\Collections\ArrayCollection;

/**
 * Interface ShipmentDataInterface
 * @package Ekyna\Component\Commerce\Shipment\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface ShipmentDataInterface
{
    /**
     * Returns the weight.
     *
     * @return float
     */
    public function getWeight();

    /**
     * Sets the weight.
     *
     * @param float $weight
     *
     * @return $this|ShipmentDataInterface
     */
    public function setWeight($weight);

    /**
     * Returns the valorization.
     *
     * @return float
     */
    public function getValorization();

    /**
     * Sets the valorization.
     *
     * @param float $valorization
     */
    public function setValorization($valorization);

    /**
     * Returns the tracking number.
     *
     * @return string
     */
    public function getTrackingNumber();

    /**
     * Sets the tracking number.
     *
     * @param string $number
     *
     * @return $this|ShipmentDataInterface
     */
    public function setTrackingNumber($number);

    /**
     * Returns the labels.
     *
     * @return ArrayCollection|ShipmentLabelInterface[]
     */
    public function getLabels();

    /**
     * Returns whether the shipment/parcel has label(s).
     *
     * @return bool
     */
    public function hasLabels();

    /**
     * Returns whether the shipment/parcel has the given label.
     *
     * @param ShipmentLabelInterface $label
     *
     * @return bool
     */
    public function hasLabel(ShipmentLabelInterface $label);

    /**
     * Adds the label.
     *
     * @param ShipmentLabelInterface $label
     *
     * @return $this|ShipmentDataInterface
     */
    public function addLabel(ShipmentLabelInterface $label);

    /**
     * Removes the label.
     *
     * @param ShipmentLabelInterface $label
     *
     * @return $this|ShipmentDataInterface
     */
    public function removeLabel(ShipmentLabelInterface $label);
}
