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
    private $formId;

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
     * @var string
     */
    private $unit;

    /**
     * @var float
     */
    private $quantity;

    /**
     * @var string
     */
    private $base;

    /**
     * @var string
     */
    private $taxRates;

    /**
     * @var string
     */
    private $taxAmount;

    /**
     * @var string
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
     * Constructor.
     *
     * @param int    $id
     * @param int    $formId
     * @param int    $number
     * @param int    $level
     * @param string $designation
     * @param string $reference
     * @param string $unit
     * @param float  $quantity
     * @param string $base
     * @param string $taxRates
     * @param string $taxAmount
     * @param string $total
     * @param array  $lines
     * @param bool   $node
     */
    public function __construct(
        $id,
        $formId,
        $number,
        $level,
        $designation,
        $reference,
        $unit,
        $quantity,
        $base,
        $taxRates,
        $taxAmount,
        $total,
        array $lines = [],
        $node = false
    ) {
        $this->id = $id;
        $this->formId = $formId;
        $this->number = $number;
        $this->level = $level;
        $this->designation = $designation;
        $this->reference = $reference;
        $this->unit = $unit;
        $this->quantity = $quantity;
        $this->base = $base;
        $this->taxRates = $taxRates;
        $this->taxAmount = $taxAmount;
        $this->total = $total;
        $this->lines = $lines;
        $this->node = $node;
    }

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
     * Returns the form id.
     *
     * @return int
     */
    public function getFormId()
    {
        return $this->formId;
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
     * @return string
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
     * @return string
     */
    public function getBase()
    {
        return $this->base;
    }

    /**
     * Returns the tax rate.
     *
     * @return string
     */
    public function getTaxRates()
    {
        return $this->taxRates;
    }

    /**
     * Returns the tax.
     *
     * @return string
     */
    public function getTaxAmount()
    {
        return $this->taxAmount;
    }

    /**
     * Returns the total.
     *
     * @return string
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
}
