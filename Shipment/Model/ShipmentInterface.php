<?php

namespace Ekyna\Component\Commerce\Shipment\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Ekyna\Component\Commerce\Order\Model\OrderInterface;

/**
 * Interface ShipmentInterface
 * @package Ekyna\Component\Commerce\Shipment\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface ShipmentInterface
{
    /**
     * Returns the id.
     *
     * @return int
     */
    public function getId();

    /**
     * Returns the order.
     *
     * @return OrderInterface
     */
    public function getOrder();

    /**
     * Sets the order.
     *
     * @param OrderInterface $order
     *
     * @return $this|ShipmentInterface
     */
    public function setOrder($order);

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
     * @return $this
     */
    public function removeItem(ShipmentItemInterface $item);

    /**
     * Sets the items.
     *
     * @param ArrayCollection|ShipmentItemInterface[] $items
     *
     * @return $this|ShipmentInterface
     */
    public function setItems(ArrayCollection $items);

    /**
     * Returns the number.
     *
     * @return string
     */
    public function getNumber();

    /**
     * Sets the number.
     *
     * @param string $number
     *
     * @return $this|ShipmentInterface
     */
    public function setNumber($number);

    /**
     * Returns the state.
     *
     * @return string
     */
    public function getState();

    /**
     * Sets the state.
     *
     * @param string $state
     *
     * @return $this|ShipmentInterface
     */
    public function setState($state);

    /**
     * Returns the createdAt.
     *
     * @return \DateTime
     */
    public function getCreatedAt();

    /**
     * Sets the createdAt.
     *
     * @param \DateTime $createdAt
     *
     * @return $this|ShipmentInterface
     */
    public function setCreatedAt(\DateTime $createdAt = null);

    /**
     * Returns the updateAt.
     *
     * @return \DateTime
     */
    public function getUpdatedAt();

    /**
     * Sets the updateAt.
     *
     * @param \DateTime $updateAt
     *
     * @return $this|ShipmentInterface
     */
    public function setUpdatedAt(\DateTime $updateAt = null);

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
