<?php

namespace Ekyna\Component\Commerce\Common\Model;

use Doctrine\Common\Collections\Collection;
use Ekyna\Component\Commerce\Subject\Model\SubjectRelativeInterface;
use Ekyna\Component\Resource\Model\SortableInterface;

/**
 * Interface SaleItemInterface
 * @package Ekyna\Component\Commerce\Common\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface SaleItemInterface extends SubjectRelativeInterface, SortableInterface, AdjustableInterface
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
     * A compound item price/stock is determined by composition (children item).
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
     * Returns whether the item is the last one (by position).
     *
     * @return bool
     */
    public function isLast(): bool;

    /**
     * Returns the unique hash.
     *
     * @param bool $encode Whether to return the plain array data or the encoded string.
     *
     * @return array|string
     */
    public function getHash(bool $encode = true);
}
