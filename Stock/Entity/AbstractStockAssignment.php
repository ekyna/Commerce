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
    public function getSoldQuantity()
    {
        return $this->soldQuantity;
    }

    /**
     * @inheritdoc
     */
    public function setSoldQuantity($quantity)
    {
        $this->soldQuantity = (float)$quantity;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getShippedQuantity()
    {
        return $this->shippedQuantity;
    }

    /**
     * @inheritdoc
     */
    public function setShippedQuantity($quantity)
    {
        $this->shippedQuantity = (float)$quantity;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getShippableQuantity()
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
    public function isFullyShipped()
    {
        return $this->soldQuantity === $this->shippedQuantity;
    }

    /**
     * @inheritdoc
     */
    public function isFullyShippable()
    {
        return $this->getShippableQuantity() >= $this->soldQuantity - $this->shippedQuantity;
    }
}
