<?php

namespace Ekyna\Component\Commerce\Shipment\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Ekyna\Component\Commerce\Common\Model as Common;
use Ekyna\Component\Resource\Model as Resource;

/**
 * Interface ShipmentInterface
 * @package Ekyna\Component\Commerce\Shipment\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface ShipmentInterface extends
    Resource\ResourceInterface,
    Resource\TimestampableInterface,
    Common\NumberSubjectInterface,
    Common\StateSubjectInterface
{
    /**
     * Returns the sale.
     *
     * @return Common\SaleInterface|ShipmentSubjectInterface
     */
    public function getSale();

    /**
     * Returns the method.
     *
     * @return ShipmentMethodInterface
     */
    public function getMethod();

    /**
     * Sets the method.
     *
     * @param ShipmentMethodInterface $method
     *
     * @return $this|ShipmentInterface
     */
    public function setMethod(ShipmentMethodInterface $method);

    /**
     * Returns whether the shipment has at least one item or not.
     *
     * @return bool
     */
    public function hasItems();

    /**
     * Returns the items.
     *
     * @return ArrayCollection|ShipmentItemInterface[]
     */
    public function getItems();

    /**
     * Returns whether the shipment has the item or not.
     *
     * @param ShipmentItemInterface $item
     *
     * @return bool
     */
    public function hasItem(ShipmentItemInterface $item);

    /**
     * Adds the item.
     *
     * @param ShipmentItemInterface $item
     *
     * @return $this
     */
    public function addItem(ShipmentItemInterface $item);

    /**
     * Removes the item.
     *
     * @param ShipmentItemInterface $item
     *
     * @return $this|ShipmentInterface
     */
    public function removeItem(ShipmentItemInterface $item);

    /**
     * Returns whether or not the shipment is a return.
     *
     * @return bool
     */
    public function isReturn();

    /**
     * Sets whether or not the shipment is a return.
     *
     * @param bool $return
     *
     * @return $this|ShipmentInterface
     */
    public function setReturn($return);

    /**
     * Returns the description.
     *
     * @return string
     */
    public function getDescription();

    /**
     * Sets the description.
     *
     * @param string $description
     *
     * @return $this|ShipmentInterface
     */
    public function setDescription($description);

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
     * @return $this|ShipmentInterface
     */
    public function setTrackingNumber($number);

    /**
     * Returns the completedAt.
     *
     * @return \DateTime
     */
    public function getCompletedAt();

    /**
     * Sets the completedAt.
     *
     * @param \DateTime $completedAt
     *
     * @return $this|ShipmentInterface
     */
    public function setCompletedAt(\DateTime $completedAt = null);
}
