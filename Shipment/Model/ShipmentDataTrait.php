<?php

namespace Ekyna\Component\Commerce\Shipment\Model;

use Doctrine\Common\Collections\ArrayCollection;

/**
 * Trait ShipmentDataTrait
 * @package Ekyna\Component\Commerce\Shipment\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
trait ShipmentDataTrait
{
    /**
     * @var float
     */
    protected $weight;

    /**
     * @var float
     */
    protected $valorization;

    /**
     * @var string
     */
    protected $trackingNumber;

    /**
     * @var ArrayCollection|ShipmentLabelInterface[]
     */
    protected $labels;


    /**
     * Initializes the shipment data.
     */
    protected function initializeShipmentData()
    {
        $this->labels = new ArrayCollection();
    }

    /**
     * Returns the weight.
     *
     * @return float
     */
    public function getWeight()
    {
        return $this->weight;
    }

    /**
     * Sets the weight.
     *
     * @param float $weight
     *
     * @return $this|ShipmentDataInterface
     */
    public function setWeight($weight)
    {
        $this->weight = (float)$weight;

        return $this;
    }

    /**
     * Returns the valorization.
     *
     * @return float
     */
    public function getValorization()
    {
        return $this->valorization;
    }

    /**
     * Sets the valorization.
     *
     * @param float $valorization
     */
    public function setValorization($valorization)
    {
        $this->valorization = (float)$valorization;
    }

    /**
     * Returns the tracking number.
     *
     * @return string
     */
    public function getTrackingNumber()
    {
        return $this->trackingNumber;
    }

    /**
     * Sets the tracking number.
     *
     * @param string $number
     *
     * @return $this|ShipmentDataInterface
     */
    public function setTrackingNumber($number)
    {
        $this->trackingNumber = $number;

        return $this;
    }

    /**
     * Returns whether the shipment/parcel has labels.
     *
     * @return bool
     */
    public function hasLabels()
    {
        return 0 < $this->labels->count();
    }

    /**
     * Returns the labels.
     *
     * @return ArrayCollection|ShipmentLabelInterface[]
     */
    public function getLabels()
    {
        return $this->labels;
    }

    /**
     * Returns whether the shipment/parcel has the given label.
     *
     * @param ShipmentLabelInterface $label
     *
     * @return bool
     */
    public function hasLabel(ShipmentLabelInterface $label)
    {
        return $this->labels->contains($label);
    }

    /**
     * Adds the label.
     *
     * @param ShipmentLabelInterface $label
     *
     * @return $this|ShipmentDataInterface
     */
    public function addLabel(ShipmentLabelInterface $label)
    {
        if (!$this->hasLabel($label)) {
            $this->labels->add($label);

            if ($this instanceof ShipmentInterface) {
                $label->setShipment($this)->setParcel(null);
            } else {
                $label->setParcel($this)->setShipment(null);
            }
        }

        return $this;
    }

    /**
     * Removes the label.
     *
     * @param ShipmentLabelInterface $label
     *
     * @return $this|ShipmentDataInterface
     */
    public function removeLabel(ShipmentLabelInterface $label)
    {
        if ($this->hasLabel($label)) {
            $this->labels->removeElement($label);

            if ($this instanceof ShipmentInterface) {
                $label->setShipment(null);
            } else {
                $label->setParcel(null);
            }
        }

        return $this;
    }
}
