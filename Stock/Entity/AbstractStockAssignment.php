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
     * @var AbstractStockUnit
     */
    protected $stockUnit;

    /**
     * @var float
     */
    protected $reservedQuantity = 0;

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
        if ($this->stockUnit !== $stockUnit) {
            $previous = $this->stockUnit;
            $this->stockUnit = $stockUnit;

            if ($previous) {
                $previous->removeStockAssignment($this);
            }

            if ($this->stockUnit) {
                $this->stockUnit->addStockAssignment($this);
            }
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getReservedQuantity()
    {
        return $this->reservedQuantity;
    }

    /**
     * @inheritdoc
     */
    public function setReservedQuantity($quantity)
    {
        $this->reservedQuantity = (float)$quantity;

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

        $quantity = $this->reservedQuantity - $this->shippedQuantity;
        if (0 > $quantity) $quantity = 0;

        return min($quantity, $this->stockUnit->getShippableQuantity());
    }
}
