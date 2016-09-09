<?php

namespace Ekyna\Component\Commerce\Common\Model;

use Doctrine\Common\Collections\Collection;
use Ekyna\Component\Resource\Model\ResourceInterface;

/**
 * Interface SaleItemInterface
 * @package Ekyna\Component\Commerce\Common\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface SaleItemInterface extends ResourceInterface, AdjustableInterface
{
    /**
     * Returns the sale.
     *
     * @return SaleInterface
     */
    public function getSale();

    /**
     * Sets the parent.
     *
     * @param SaleItemInterface $parent
     * @return $this|SaleItemInterface
     * @internal
     */
    public function setParent(SaleItemInterface $parent = null);

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
     * Adds the child item.
     *
     * @param SaleItemInterface $child
     *
     * @return $this|SaleItemInterface
     */
    public function addChild(SaleItemInterface $child);

    /**
     * Removes the child item.
     *
     * @param SaleItemInterface $child
     *
     * @return $this|SaleItemInterface
     */
    public function removeChild(SaleItemInterface $child);

    /**
     * Returns the children items.
     *
     * @return Collection|SaleItemInterface[]
     */
    public function getChildren();

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
     * @return $this|SaleItemInterface
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
     * @return $this|SaleItemInterface
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
     * @return $this|SaleItemInterface
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
     * @return $this|SaleItemInterface
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
     * @return $this|SaleItemInterface
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
     * @return $this|SaleItemInterface
     */
    public function setPosition($position);

    /**
     * Returns whether the item has a subject data or not.
     *
     * @return bool
     */
    public function hasSubjectData();

    /**
     * Returns the subject data.
     *
     * @return array|null
     */
    public function getSubjectData();

    /**
     * Sets the subject data.
     *
     * @param array|null $data
     *
     * @return $this|SaleItemInterface
     */
    public function setSubjectData(array $data = null);

    /**
     * Returns the subject (may return null if it has not been resolved yet).
     *
     * @return object|null
     * @internal
     */
    public function getSubject();

    /**
     * Sets the subject.
     *
     * @param object $subject
     *
     * @return $this|SaleItemInterface
     * @internal
     */
    public function setSubject($subject = null);
}
