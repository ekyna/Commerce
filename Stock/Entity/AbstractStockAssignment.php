<?php

namespace Ekyna\Component\Commerce\Stock\Entity;

use Ekyna\Component\Commerce\Stock\Model as Stock;

/**
 * Class AbstractStockAssignment
 * @package Ekyna\Component\Commerce\Stock\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
abstract class AbstractStockAssignment implements Stock\StockAssignmentInterface
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var Stock\StockUnitInterface
     */
    protected $stockUnit;

    /**
     * @var float
     */
    protected $soldQuantity;

    /**
     * @var float
     */
    protected $shippedQuantity;

    /**
     * @var float
     */
    protected $lockedQuantity;


    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->soldQuantity = 0.;
        $this->shippedQuantity = 0.;
        $this->lockedQuantity = 0.;
    }

    /**
     * @inheritdoc
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @inheritdoc
     */
    public function getStockUnit()
    {
        return $this->stockUnit;
    }

    /**
     * @inheritdoc
     */
    public function setStockUnit(Stock\StockUnitInterface $stockUnit = null)
    {
        if ($stockUnit !== $this->stockUnit) {
            if ($previous = $this->stockUnit) {
                $this->stockUnit = null;
                $previous->removeStockAssignment($this);
            }

            if ($this->stockUnit = $stockUnit) {
                $this->stockUnit->addStockAssignment($this);
            }
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getSoldQuantity(): float
    {
        return $this->soldQuantity;
    }

    /**
     * @inheritdoc
     */
    public function setSoldQuantity(float $quantity): Stock\StockAssignmentInterface
    {
        $this->soldQuantity = $quantity;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getShippedQuantity(): float
    {
        return $this->shippedQuantity;
    }

    /**
     * @inheritdoc
     */
    public function setShippedQuantity(float $quantity): Stock\StockAssignmentInterface
    {
        $this->shippedQuantity = $quantity;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getLockedQuantity(): float
    {
        return $this->lockedQuantity;
    }

    /**
     * @inheritdoc
     */
    public function setLockedQuantity(float $quantity): Stock\StockAssignmentInterface
    {
        $this->lockedQuantity = $quantity;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getShippableQuantity(): float
    {
        if (!$this->stockUnit) {
            return 0;
        }

        return min(
            max(0, $this->soldQuantity - $this->shippedQuantity - $this->lockedQuantity),
            $this->stockUnit->getShippableQuantity()
        );
    }

    /**
     * @inheritdoc
     */
    public function getReleasableQuantity(): float
    {
        if (!$unit = $this->stockUnit) {
            return 0.;
        }

        // Sold - Shipped - Locked
        return max(0, $this->soldQuantity - $this->shippedQuantity - $this->lockedQuantity);
    }

    /**
     * @inheritdoc
     */
    public function isFullyShipped(): bool
    {
        // TODO Use packaging format
        return 0 === bccomp($this->soldQuantity, $this->shippedQuantity, 5);
    }

    /**
     * @inheritdoc
     */
    public function isFullyShippable(): bool
    {
        // TODO Use packaging format
        return 0 <= bccomp($this->getShippableQuantity() + $this->lockedQuantity, $this->soldQuantity - $this->shippedQuantity, 5);
    }

    /**
     * @inheritdoc
     */
    public function isEmpty(): bool
    {
        return 0 == $this->soldQuantity
            && 0 == $this->shippedQuantity
            && 0 == $this->lockedQuantity;
    }
}
