<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Stock\Entity;

use Decimal\Decimal;
use Ekyna\Component\Commerce\Stock\Model as Stock;
use Ekyna\Component\Commerce\Stock\Model\StockAssignmentInterface;

/**
 * Class AbstractStockAssignment
 * @package Ekyna\Component\Commerce\Stock\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
abstract class AbstractStockAssignment implements Stock\StockAssignmentInterface
{
    protected ?int                      $id        = null;
    protected ?Stock\StockUnitInterface $stockUnit = null;
    protected Decimal                   $soldQuantity;
    protected Decimal                   $shippedQuantity;
    protected Decimal                   $lockedQuantity;

    public function __construct()
    {
        $this->soldQuantity = new Decimal(0);
        $this->shippedQuantity = new Decimal(0);
        $this->lockedQuantity = new Decimal(0);
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStockUnit(): ?Stock\StockUnitInterface
    {
        return $this->stockUnit;
    }

    public function setStockUnit(?Stock\StockUnitInterface $stockUnit): StockAssignmentInterface
    {
        if ($stockUnit === $this->stockUnit) {
            return $this;
        }

        if ($previous = $this->stockUnit) {
            $this->stockUnit = null;
            $previous->removeStockAssignment($this);
        }

        if ($this->stockUnit = $stockUnit) {
            $this->stockUnit->addStockAssignment($this);
        }

        return $this;
    }

    public function getSoldQuantity(): Decimal
    {
        return $this->soldQuantity;
    }

    public function setSoldQuantity(Decimal $quantity): Stock\StockAssignmentInterface
    {
        $this->soldQuantity = $quantity;

        return $this;
    }

    public function getShippedQuantity(): Decimal
    {
        return $this->shippedQuantity;
    }

    public function setShippedQuantity(Decimal $quantity): Stock\StockAssignmentInterface
    {
        $this->shippedQuantity = $quantity;

        return $this;
    }

    public function getLockedQuantity(): Decimal
    {
        return $this->lockedQuantity;
    }

    public function setLockedQuantity(Decimal $quantity): Stock\StockAssignmentInterface
    {
        $this->lockedQuantity = $quantity;

        return $this;
    }

    public function getShippableQuantity(): Decimal
    {
        if (null === $this->stockUnit) {
            return new Decimal(0);
        }

        return min(
            max(new Decimal(0), $this->soldQuantity - $this->shippedQuantity - $this->lockedQuantity),
            $this->stockUnit->getShippableQuantity()
        );
    }

    public function getReleasableQuantity(): Decimal
    {
        if (null === $this->stockUnit) {
            return new Decimal(0);
        }

        // Sold - Shipped - Locked
        return max(new Decimal(0), $this->soldQuantity - $this->shippedQuantity - $this->lockedQuantity);
    }

    public function isFullyShipped(): bool
    {
        return $this->soldQuantity->equals($this->shippedQuantity);
    }

    public function isFullyShippable(): bool
    {
        // TODO Use packaging format
        return $this->getShippableQuantity()->add($this->lockedQuantity)
            >= $this->soldQuantity->sub($this->shippedQuantity);
    }

    public function isEmpty(): bool
    {
        return $this->soldQuantity->isZero()
            && $this->shippedQuantity->isZero()
            && $this->lockedQuantity->isZero();
    }
}
