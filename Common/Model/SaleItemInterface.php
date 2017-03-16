<?php

namespace Ekyna\Component\Commerce\Common\Model;

use Doctrine\Common\Collections\Collection;
use Ekyna\Component\Commerce\Pricing\Model\TaxableInterface;
use Ekyna\Component\Commerce\Subject\Model\SubjectRelativeInterface;
use Ekyna\Component\Resource\Model as ResourceModel;

/**
 * Interface SaleItemInterface
 * @package Ekyna\Component\Commerce\Common\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface SaleItemInterface extends
    ResourceModel\ResourceInterface,
    ResourceModel\SortableInterface,
    SubjectRelativeInterface,
    TaxableInterface,
    AdjustableInterface
{
    /**
     * Sets the sale.
     *
     * @param SaleInterface $sale
     *
     * @return $this|SaleItemInterface
     */
    public function setSale(SaleInterface $sale = null);

    /**
     * Returns the sale.
     *
     * @return SaleInterface|null
     */
    public function getSale();

    /**
     * Sets the parent.
     *
     * @param SaleItemInterface $parent
     *
     * @return $this|SaleItemInterface
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
     * Returns the net price.
     *
     * @return float
     */
    public function getNetPrice();

    /**
     * Sets the net price.
     *
     * @param float $netPrice
     *
     * @return $this|SaleItemInterface
     */
    public function setNetPrice($netPrice);

    /**
     * Returns the weight (kilograms).
     *
     * @return float
     */
    public function getWeight();

    /**
     * Sets the weight (kilograms).
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
     * Returns whether the item is immutable or not.
     *
     * @return boolean
     */
    public function isImmutable();

    /**
     * Sets the immutable.
     *
     * @param boolean $immutable
     *
     * @return $this|SaleItemInterface
     */
    public function setImmutable($immutable);

    /**
     * Returns the configurable.
     *
     * @return boolean
     */
    public function isConfigurable();

    /**
     * Sets the configurable.
     *
     * @param boolean $configurable
     *
     * @return $this|SaleItemInterface
     */
    public function setConfigurable($configurable);

    /**
     * Returns the parents total quantity.
     *
     * @return float
     */
    public function getParentsQuantity();

    /**
     * Returns the total quantity (multiplied by all parents quantities).
     *
     * @return float
     */
    public function getTotalQuantity();
}
