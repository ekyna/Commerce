<?php

namespace Ekyna\Component\Commerce\Common\Model;

use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Comparable;
use Ekyna\Component\Commerce\Common\Calculator\Amount;
use Ekyna\Component\Commerce\Common\Calculator\Margin;
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
    AdjustableInterface,
    Comparable
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
     * Creates a child item.
     *
     * @return SaleItemInterface
     */
    public function createChild();

    /**
     * Returns whether the current item has the given child item.
     *
     * @param SaleItemInterface $child
     *
     * @return $this|SaleItemInterface
     */
    public function hasChild(SaleItemInterface $child);

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
     * @return $this|SaleItemInterface
     */
    public function setDescription($description);

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
     * Returns whether the item is compound.
     *
     * @return bool
     */
    public function isCompound();

    /**
     * Sets whether the item is compound.
     *
     * @param bool $compound
     *
     * @return $this|SaleItemInterface
     */
    public function setCompound($compound);

    /**
     * Returns whether the item is immutable.
     *
     * @return boolean
     */
    public function isImmutable();

    /**
     * Sets whether the item is immutable.
     *
     * @param boolean $immutable
     *
     * @return $this|SaleItemInterface
     */
    public function setImmutable($immutable);

    /**
     * Returns whether the item is configurable.
     *
     * @return boolean
     */
    public function isConfigurable();

    /**
     * Sets whether the item is configurable.
     *
     * @param boolean $configurable
     *
     * @return $this|SaleItemInterface
     */
    public function setConfigurable($configurable);

    /**
     * Returns the private.
     *
     * @return bool
     */
    public function isPrivate();

    /**
     * Sets the private.
     *
     * @param bool $private
     *
     * @return $this|SaleItemInterface
     */
    public function setPrivate($private);

    /**
     * Returns whether or not the item has at least one private child.
     *
     * @return bool
     */
    public function hasPrivateChildren();

    /**
     * Returns whether or not the item has at least one public child.
     *
     * @return bool
     */
    public function hasPublicChildren();

    /**
     * Returns whether the item has data (optionally for the given key).
     *
     * @param string $key
     *
     * @return bool
     */
    public function hasData($key = null);

    /**
     * Returns the data, optionally filtered by key.
     *
     * @param string $key
     *
     * @return mixed
     */
    public function getData($key = null);

    /**
     * Sets the data.
     *
     * @param array|string $keyOrData The key of the data or the whole data as array.
     * @param mixed        $data      The data assigned to the key (must be null if $keyOrData is the whole data).
     *
     * @return $this|SaleItemInterface
     */
    public function setData($keyOrData, $data = null);

    /**
     * Unset the data by key.
     *
     * @param string $key
     *
     * @return $this|SaleItemInterface
     */
    public function unsetData($key);

    /**
     * Returns the item level in the sale hierarchy.
     *
     * @return float
     */
    public function getLevel();

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

    /**
     * Clears the results.
     *
     * @return $this|SaleItemInterface
     *
     * @internal Usage reserved to calculator.
     */
    public function clearResult();

    /**
     * Sets the result.
     *
     * @param Amount $result
     *
     * @return $this|SaleItemInterface
     *
     * @internal Usage reserved to calculator.
     */
    public function setResult(Amount $result);

    /**
     * Returns the result.
     *
     * @return Amount
     *
     * @internal Usage reserved to view builder.
     */
    public function getResult();

    /**
     * Sets the margin.
     *
     * @param Margin $margin
     */
    public function setMargin(Margin $margin);

    /**
     * Returns the margin.
     *
     * @return Margin
     */
    public function getMargin();

    /**
     * Returns whether the item is the last one (by position).
     *
     * @return bool
     */
    public function isLast();

    /**
     * Returns the unique hash.
     *
     * @param bool $encode Whether to return the plain array data or the encoded string.
     *
     * @return array|string
     */
    public function getHash($encode = true);
}
