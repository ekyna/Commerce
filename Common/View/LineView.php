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
     * @var string
     */
    private $id;

    /**
     * @var string
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
    private $description;

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
    private $lines = [];

    /**
     * @var bool
     */
    private $node = false;


    /**
     * Constructor.
     *
     * @param string $id
     * @param string $formId
     * @param int    $number
     * @param int    $level
     */
    public function __construct($id, $formId, $number, $level = 0)
    {
        $this->id = $id;
        $this->formId = $formId;
        $this->number = $number;
        $this->level = $level;
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
     * Sets the designation.
     *
     * @param string $designation
     *
     * @return LineView
     */
    public function setDesignation($designation)
    {
        $this->designation = $designation;

        return $this;
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
     * Returns the description.
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Sets the description.
     *
     * @param string $description
     *
     * @return LineView
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Sets the reference.
     *
     * @param string $reference
     *
     * @return LineView
     */
    public function setReference($reference)
    {
        $this->reference = $reference;

        return $this;
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
     * Sets the unit.
     *
     * @param string $unit
     *
     * @return LineView
     */
    public function setUnit($unit)
    {
        $this->unit = $unit;

        return $this;
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
     * Sets the quantity.
     *
     * @param float $quantity
     *
     * @return LineView
     */
    public function setQuantity($quantity)
    {
        $this->quantity = $quantity;

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
     * Sets the base.
     *
     * @param string $base
     *
     * @return LineView
     */
    public function setBase($base)
    {
        $this->base = $base;

        return $this;
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
     * Sets the tax rates.
     *
     * @param string $rates
     *
     * @return LineView
     */
    public function setTaxRates($rates)
    {
        $this->taxRates = $rates;

        return $this;
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
     * Sets the taxAmount.
     *
     * @param string $amount
     *
     * @return LineView
     */
    public function setTaxAmount($amount)
    {
        $this->taxAmount = $amount;

        return $this;
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
     * Sets the total.
     *
     * @param string $total
     *
     * @return LineView
     */
    public function setTotal($total)
    {
        $this->total = $total;

        return $this;
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
     * Adds the line.
     *
     * @param LineView $line
     *
     * @return LineView
     */
    public function addLine(LineView $line)
    {
        $this->lines[] = $line;

        return $this;
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
     * Sets the node.
     *
     * @param bool $node
     *
     * @return LineView
     */
    public function setNode($node)
    {
        $this->node = (bool)$node;

        return $this;
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
