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
    protected $soldQuantity = 0;

    /**
     * @var float
     */
    protected $shippedQuantity = 0;


    /**
     * @inheritdoc
     */
    public function getId()
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
    public function getShippableQuantity(): float
    {
        if (!$this->stockUnit) {
            return 0;
        }

        $quantity = $this->soldQuantity - $this->shippedQuantity;
        if (0 > $quantity) $quantity = 0;

        return min($quantity, $this->stockUnit->getShippableQuantity());
    }

    /**
     * @inheritdoc
     */
    public function isFullyShipped(): bool
    {
        return 0 === bccomp($this->soldQuantity, $this->shippedQuantity, 5);
    }

    /**
     * @inheritdoc
     */
    public function isFullyShippable(): bool
    {
        //return $this->getShippableQuantity() >= $this->soldQuantity - $this->shippedQuantity;
        return 0 <= bccomp($this->getShippableQuantity(), $this->soldQuantity - $this->shippedQuantity, 5);
    }

    /**
     * @inheritdoc
     */
    public function isEmpty(): bool
    {
        return 0 == $this->soldQuantity
            && 0 == $this->shippedQuantity;
    }
}
