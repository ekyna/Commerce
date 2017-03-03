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
    protected $quantity;


    /**
     * Returns the id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Returns the stock unit.
     *
     * @return Stock\StockUnitInterface
     */
    public function getStockUnit()
    {
        return $this->stockUnit;
    }

    /**
     * Sets the stock unit.
     *
     * @param Stock\StockUnitInterface $stockUnit
     *
     * @return AbstractStockAssignment
     */
    public function setStockUnit(Stock\StockUnitInterface $stockUnit)
    {
        $this->stockUnit = $stockUnit;

        return $this;
    }

    /**
     * Returns the quantity.
     *
     * @return float
     */
    public function getQuantity()
    {
        return $this->quantity;
    }

    /**
     * Sets the quantity.
     *
     * @param float $quantity
     *
     * @return AbstractStockAssignment
     */
    public function setQuantity($quantity)
    {
        $this->quantity = (float)$quantity;

        return $this;
    }
}
