<?php

namespace Ekyna\Component\Commerce\Common\Model;

use Doctrine\Common\Collections\Collection;
use Ekyna\Component\Resource\Model\ResourceInterface;

/**
 * Interface SaleItemInterface
 * @package Ekyna\Component\Commerce\Common\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface SaleItemInterface extends ResourceInterface
{
    /**
     * Returns the parent item.
     *
     * @return SaleItemInterface|null
     */
    public function getParent();

    /**
     * Returns whether the item has children or not.
     *
     * @return bool
     */
    public function hasChildren();

    /**
     * Returns the children items.
     *
     * @return Collection|SaleItemInterface[]
     */
    public function getChildren();

    /**
     * Returns the item adjustments.
     *
     * @return Collection|AdjustmentInterface[]
     */
    public function getAdjustments();

    /**
     * Returns the designation.
     *
     * @return string
     */
    public function getDesignation();

    /**
     * Sets the designation.
     *
     * @param string $designation
     *
*@return $this|SaleItemInterface
     */
    public function setDesignation($designation);

    /**
     * Returns the reference.
     *
     * @return string
     */
    public function getReference();

    /**
     * Sets the reference.
     *
     * @param string $reference
     *
*@return $this|SaleItemInterface
     */
    public function setReference($reference);

    /**
     * Returns the netPrice.
     *
     * @return float
     */
    public function getNetPrice();

    /**
     * Sets the netPrice.
     *
     * @param float $netPrice
     *
*@return $this|SaleItemInterface
     */
    public function setNetPrice($netPrice);

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
*@return $this|SaleItemInterface
     */
    public function setWeight($weight);

    /**
     * Returns the quantity.
     *
     * @return float
     */
    public function getQuantity();

    /**
     * Sets the quantity.
     *
     * @param float $quantity
     *
*@return $this|SaleItemInterface
     */
    public function setQuantity($quantity);

    /**
     * Returns the position.
     *
     * @return int
     */
    public function getPosition();

    /**
     * Sets the position.
     *
     * @param int $position
     *
*@return $this|SaleItemInterface
     */
    public function setPosition($position);
}
