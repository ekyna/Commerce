<?php

namespace Ekyna\Component\Commerce\Common\View;

/**
 * Class LineView
 * @package Ekyna\Component\Commerce\Common\View
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class LineView extends AbstractView
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var int
     */
    private $number;

    /**
     * @var int
     */
    private $level;

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
    private $taxRate;

    /**
     * @var float
     */
    private $taxAmount;

    /**
     * @var float
     */
    private $total;

    /**
     * @var LineView[]
     */
    private $lines;

    /**
     * @var bool
     */
    private $node = false;

    /**
     * @var bool
     */
    private $immutable = false;


    /**
     * Constructor.
     *
     * @param int    $id
     * @param int    $number
     * @param int    $level
     * @param string $designation
     * @param string $reference
     * @param float  $unit
     * @param float  $quantity
     * @param float  $base
     * @param float  $taxRate
     * @param float  $taxAmount
     * @param float  $total
     * @param array  $lines
     * @param bool   $node
     * @param bool   $immutable
     */
    public function __construct(
        $id,
        $number,
        $level,
        $designation,
        $reference,
        $unit,
        $quantity,
        $base,
        $taxRate,
        $taxAmount,
        $total,
        array $lines = [],
        $node = false,
        $immutable = false
    ) {
        $this->id = $id;
        $this->number = $number;
        $this->level = $level;
        $this->designation = $designation;
        $this->reference = $reference;
        $this->unit = $unit;
        $this->quantity = $quantity;
        $this->base = $base;
        $this->taxRate = $taxRate;
        $this->taxAmount = $taxAmount;
        $this->total = $total;
        $this->lines = $lines;
        $this->node = $node;
        $this->immutable = $immutable;
    }

    /**
     * Returns the id of the source element.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Returns the number.
     *
     * @return int
     */
    public function getNumber()
    {
        return $this->number;
    }

    /**
     * Returns the level.
     *
     * @return int
     */
    public function getLevel()
    {
        return $this->level;
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
     * Returns the tax rate.
     *
     * @return float
     */
    public function getTaxRate()
    {
        return $this->taxRate;
    }

    /**
     * Returns the tax.
     *
     * @return float
     */
    public function getTaxAmount()
    {
        return $this->taxAmount;
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
     * @return LineView[]
     */
    public function getLines()
    {
        return $this->lines;
    }

    /**
     * Returns whether the line is node or leaf.
     *
     * @return boolean
     */
    public function isNode()
    {
        return $this->node;
    }

    /**
     * Returns whether the line is immutable or not.
     *
     * @return boolean
     */
    public function isImmutable()
    {
        return $this->immutable;
    }
}
