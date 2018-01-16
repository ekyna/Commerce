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
    private $availability;

    /**
     * The unit price.
     *
     * @var string
     */
    private $unit;

    /**
     * @var float
     */
    private $quantity;

    /**
     * Total price before applying discounts and taxes.
     *
     * @var string
     */
    private $gross;

    /**
     * @var string
     */
    private $discountRates;

    /**
     * @var string
     */
    private $discountAmount;

    /**
     * Total price after applying discounts and before applying taxes.
     *
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
     * Total price after applying discounts and taxes.
     *
     * @var string
     */
    private $total;

    /**
     * The margin in percentage.
     *
     * @var string
     */
    private $margin;

    /**
     * @var LineView[]
     */
    private $lines = [];

    /**
     * @var bool
     */
    private $private = false;


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

        $this->vars['actions'] = [];
    }

    /**
     * Adds the action.
     *
     * @param Action $action
     */
    public function addAction(Action $action)
    {
        $this->vars['actions'][] = $action;
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
     * Returns the availability.
     *
     * @return string
     */
    public function getAvailability()
    {
        return $this->availability;
    }

    /**
     * Sets the availability.
     *
     * @param string $availability
     *
     * @return LineView
     */
    public function setAvailability($availability)
    {
        $this->availability = $availability;

        return $this;
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
     * Returns the gross price.
     *
     * @return string
     */
    public function getGross()
    {
        return $this->gross;
    }

    /**
     * Sets the gross price.
     *
     * @param string $gross
     *
     * @return LineView
     */
    public function setGross($gross)
    {
        $this->gross = $gross;

        return $this;
    }

    /**
     * Returns the discount rates.
     *
     * @return string
     */
    public function getDiscountRates()
    {
        return $this->discountRates;
    }

    /**
     * Sets the discount rates.
     *
     * @param string $rates
     *
     * @return LineView
     */
    public function setDiscountRates($rates)
    {
        $this->discountRates = $rates;

        return $this;
    }

    /**
     * Returns the discount amount.
     *
     * @return string
     */
    public function getDiscountAmount()
    {
        return $this->discountAmount;
    }

    /**
     * Sets the discount amount.
     *
     * @param string $amount
     *
     * @return LineView
     */
    public function setDiscountAmount($amount)
    {
        $this->discountAmount = $amount;

        return $this;
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
     * Sets the margin in percentage.
     *
     * @param string $margin
     *
     * @return LineView
     */
    public function setMargin($margin)
    {
        $this->margin = $margin;

        return $this;
    }

    /**
     * Returns the margin in percentage.
     *
     * @return string
     */
    public function getMargin()
    {
        return $this->margin;
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
     * Sets the whether or not the line is private.
     *
     * @param bool $private
     *
     * @return LineView
     */
    public function setPrivate($private)
    {
        $this->private = (bool)$private;

        return $this;
    }

    /**
     * Returns whether or not the line is private.
     *
     * @return boolean
     */
    public function isPrivate()
    {
        return $this->private;
    }
}
