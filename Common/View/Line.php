<?php

namespace Ekyna\Component\Commerce\Common\View;

/**
 * Class Line
 * @package Ekyna\Component\Commerce\Common\View
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class Line
{
    /**
     * @var string
     */
    private $designation;

    /**
     * @var string
     */
    private $reference;

    /**
     * @var float
     */
    private $unit;

    /**
     * @var float
     */
    private $quantity;

    /**
     * @var float
     */
    private $base;

    /**
     * @var float
     */
    private $tax;

    /**
     * @var float
     */
    private $total;

    /**
     * @var Line[]
     */
    private $lines;

    /**
     * @var bool
     */
    private $children = false;


    /**
     * Constructor.
     *
     * @param string $designation
     * @param string $reference
     * @param float  $unit
     * @param float  $quantity
     * @param float  $base
     * @param float  $tax
     * @param float  $total
     * @param array  $lines
     * @param bool   $children
     */
    public function __construct($designation, $reference, $unit, $quantity, $base, $tax, $total, array $lines = [], $children = false)
    {
        $this->designation = $designation;
        $this->reference = $reference;
        $this->unit = $unit;
        $this->quantity = $quantity;
        $this->base = $base;
        $this->tax = $tax;
        $this->total = $total;
        $this->lines = $lines;
        $this->children = $children;
    }

    /**
     * Returns the designation.
     *
     * @return string
     */
    public function getDesignation()
    {
        return $this->designation;
    }

    /**
     * Returns the reference.
     *
     * @return string
     */
    public function getReference()
    {
        return $this->reference;
    }

    /**
     * Returns the unit.
     *
     * @return float
     */
    public function getUnit()
    {
        return $this->unit;
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
     * Returns the base.
     *
     * @return float
     */
    public function getBase()
    {
        return $this->base;
    }

    /**
     * Returns the tax.
     *
     * @return float
     */
    public function getTax()
    {
        return $this->tax;
    }

    /**
     * Returns the total.
     *
     * @return float
     */
    public function getTotal()
    {
        return $this->total;
    }

    /**
     * Returns the lines.
     *
     * @return Line[]
     */
    public function getLines()
    {
        return $this->lines;
    }

    /**
     * Returns whether the line has children (items) or not.
     *
     * @return boolean
     */
    public function isChildren()
    {
        return $this->children;
    }
}
