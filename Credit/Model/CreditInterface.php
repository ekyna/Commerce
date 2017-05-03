<?php

namespace Ekyna\Component\Commerce\Credit\Model;

use Ekyna\Component\Commerce\Common\Model as Common;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentInterface;
use Ekyna\Component\Resource\Model as Resource;

/**
 * Interface CreditInterface
 * @package Ekyna\Component\Commerce\Credit\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface CreditInterface extends
    Resource\ResourceInterface,
    Resource\TimestampableInterface,
    Common\NumberSubjectInterface
{
    /**
     * Returns the sale.
     *
     * @return Common\SaleInterface|CreditSubjectInterface
     */
    public function getSale();

    /**
     * Returns the shipment.
     *
     * @return ShipmentInterface
     */
    public function getShipment();

    /**
     * Returns whether the credit has at least one item or not.
     *
     * @return bool
     */
    public function hasItems();

    /**
     * Returns the items.
     *
     * @return \Doctrine\Common\Collections\Collection|CreditItemInterface[]
     */
    public function getItems();

    /**
     * Returns whether the credit has the item or not.
     *
     * @param CreditItemInterface $item
     *
     * @return bool
     */
    public function hasItem(CreditItemInterface $item);

    /**
     * Adds the item.
     *
     * @param CreditItemInterface $item
     *
     * @return $this
     */
    public function addItem(CreditItemInterface $item);

    /**
     * Removes the item.
     *
     * @param CreditItemInterface $item
     *
     * @return $this|CreditInterface
     */
    public function removeItem(CreditItemInterface $item);

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
     * @return $this|CreditInterface
     */
    public function setDescription($description);
}
