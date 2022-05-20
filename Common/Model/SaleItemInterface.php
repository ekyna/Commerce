<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Common\Model;

use Decimal\Decimal;
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
    public function setSale(?SaleInterface $sale): SaleItemInterface;

    public function getSale(): ?SaleInterface;

    public function hasParent(): bool;

    public function setParent(?SaleItemInterface $parent): SaleItemInterface;

    /**
     * Returns the first public ancestor (or the item itself if it is public).
     */
    public function getPublicParent(): SaleItemInterface;

    public function getParent(): ?SaleItemInterface;

    public function hasChildren(): bool;

    public function createChild(): SaleItemInterface;

    /**
     * Returns whether the current item has the given child item.
     */
    public function hasChild(SaleItemInterface $child): bool;

    public function addChild(SaleItemInterface $child): SaleItemInterface;

    public function removeChild(SaleItemInterface $child): SaleItemInterface;

    /**
     * @return array<SaleItemInterface>|Collection
     */
    public function getChildren(): Collection;

    public function getDescription(): ?string;

    public function setDescription(?string $description): SaleItemInterface;

    public function getQuantity(): Decimal;

    public function setQuantity(Decimal $quantity): SaleItemInterface;

    /**
     * A compound item price/stock is determined by composition (children item).
     */
    public function isCompound(): bool;

    public function setCompound(bool $compound): SaleItemInterface;

    public function isImmutable(): bool;

    public function setImmutable(bool $immutable): SaleItemInterface;

    public function isConfigurable(): bool;

    public function setConfigurable(bool $configurable): SaleItemInterface;

    public function isPrivate(): bool;

    public function setPrivate(bool $private): SaleItemInterface;

    /**
     * Returns whether this sale item has at least one private child.
     */
    public function hasPrivateChildren(): bool;

    /**
     * Returns whether this sale item has at least one public child.
     */
    public function hasPublicChildren(): bool;

    public function hasData(?string $key): bool;

    /**
     * @return mixed
     * @TODO PHP8 Union types
     */
    public function getData(?string $key);

    /**
     * @param array|string $keyOrData The key of the data or the whole data as array.
     * @param mixed        $data      The data assigned to the key (must be null if $keyOrData is the whole data).
     * @TODO PHP8 Union types
     */
    public function setData($keyOrData, $data = null): SaleItemInterface;

    public function unsetData(string $key): SaleItemInterface;

    /**
     * Returns the item level in the sale hierarchy.
     */
    public function getLevel(): int;

    public function getRoot(): ?SaleItemInterface;

    public function getRootSale(): ?SaleInterface;

    /**
     * Returns the parents total quantity.
     */
    public function getParentsQuantity(): Decimal;

    /**
     * Returns the total quantity (multiplied by all parents quantities).
     */
    public function getTotalQuantity(): Decimal;

    /**
     * Returns whether the item is the last one (by position).
     */
    public function isLast(): bool;

    /**
     * Returns the unique hash.
     *
     * @param bool $encode Whether to return the plain array data or the encoded string.
     *
     * @return array|string
     * @TODO PHP8 Union types
     */
    public function getHash(bool $encode = true);
}
