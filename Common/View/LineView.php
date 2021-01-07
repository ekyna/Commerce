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
     * @var string
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
     * @var object
     */
    private $source;


    /**
     * Constructor.
     *
     * @param string $id
     * @param string $formId
     * @param int    $number
     * @param int    $level
     */
    public function __construct(string $id, string $formId, int $number, int $level = 0)
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
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * Returns the form id.
     *
     * @return string
     */
    public function getFormId(): string
    {
        return $this->formId;
    }

    /**
     * Returns the number.
     *
     * @return int
     */
    public function getNumber(): int
    {
        return $this->number;
    }

    /**
     * Returns the level.
     *
     * @return int
     */
    public function getLevel(): int
    {
        return $this->level;
    }

    /**
     * Sets the designation.
     *
     * @param string|null $designation
     *
     * @return LineView
     */
    public function setDesignation(string $designation = null): self
    {
        $this->designation = $designation;

        return $this;
    }

    /**
     * Returns the designation.
     *
     * @return string
     */
    public function getDesignation(): ?string
    {
        return $this->designation;
    }

    /**
     * Sets the description.
     *
     * @param string|null $description
     *
     * @return LineView
     */
    public function setDescription(string $description = null): self
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Returns the description.
     *
     * @return string
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * Sets the reference.
     *
     * @param string|null $reference
     *
     * @return LineView
     */
    public function setReference(string $reference = null): self
    {
        $this->reference = $reference;

        return $this;
    }

    /**
     * Returns the reference.
     *
     * @return string
     */
    public function getReference(): ?string
    {
        return $this->reference;
    }

    /**
     * Sets the availability.
     *
     * @param string|null $availability
     *
     * @return LineView
     */
    public function setAvailability(string $availability = null): self
    {
        $this->availability = $availability;

        return $this;
    }

    /**
     * Returns the availability.
     *
     * @return string
     */
    public function getAvailability(): ?string
    {
        return $this->availability;
    }

    /**
     * Sets the unit.
     *
     * @param string|null $unit
     *
     * @return LineView
     */
    public function setUnit(string $unit = null): self
    {
        $this->unit = $unit;

        return $this;
    }

    /**
     * Returns the unit.
     *
     * @return string
     */
    public function getUnit(): ?string
    {
        return $this->unit;
    }

    /**
     * Sets the quantity.
     *
     * @param string|null $quantity
     *
     * @return LineView
     */
    public function setQuantity(string $quantity = null): self
    {
        $this->quantity = $quantity;

        return $this;
    }

    /**
     * Returns the quantity.
     *
     * @return string
     */
    public function getQuantity(): ?string
    {
        return $this->quantity;
    }

    /**
     * Sets the gross price.
     *
     * @param string|null $gross
     *
     * @return LineView
     */
    public function setGross(string $gross = null): self
    {
        $this->gross = $gross;

        return $this;
    }

    /**
     * Returns the gross price.
     *
     * @return string
     */
    public function getGross(): ?string
    {
        return $this->gross;
    }

    /**
     * Sets the discount rates.
     *
     * @param string|null $rates
     *
     * @return LineView
     */
    public function setDiscountRates(string $rates = null): self
    {
        $this->discountRates = $rates;

        return $this;
    }

    /**
     * Returns the discount rates.
     *
     * @return string
     */
    public function getDiscountRates(): ?string
    {
        return $this->discountRates;
    }

    /**
     * Sets the discount amount.
     *
     * @param string|null $amount
     *
     * @return LineView
     */
    public function setDiscountAmount(string $amount = null): self
    {
        $this->discountAmount = $amount;

        return $this;
    }

    /**
     * Returns the discount amount.
     *
     * @return string
     */
    public function getDiscountAmount(): ?string
    {
        return $this->discountAmount;
    }

    /**
     * Sets the base.
     *
     * @param string|null $base
     *
     * @return LineView
     */
    public function setBase(string $base = null): self
    {
        $this->base = $base;

        return $this;
    }

    /**
     * Returns the base.
     *
     * @return string
     */
    public function getBase(): ?string
    {
        return $this->base;
    }

    /**
     * Sets the tax rates.
     *
     * @param string|null $rates
     *
     * @return LineView
     */
    public function setTaxRates(string $rates = null): self
    {
        $this->taxRates = $rates;

        return $this;
    }

    /**
     * Returns the tax rate.
     *
     * @return string
     */
    public function getTaxRates(): ?string
    {
        return $this->taxRates;
    }

    /**
     * Sets the taxAmount.
     *
     * @param string|null $amount
     *
     * @return LineView
     */
    public function setTaxAmount(string $amount = null): self
    {
        $this->taxAmount = $amount;

        return $this;
    }

    /**
     * Returns the tax.
     *
     * @return string
     */
    public function getTaxAmount(): ?string
    {
        return $this->taxAmount;
    }

    /**
     * Sets the total.
     *
     * @param string|null $total
     *
     * @return LineView
     */
    public function setTotal(string $total = null): self
    {
        $this->total = $total;

        return $this;
    }

    /**
     * Returns the total.
     *
     * @return string
     */
    public function getTotal(): ?string
    {
        return $this->total;
    }

    /**
     * Sets the margin in percentage.
     *
     * @param string|null $margin
     *
     * @return LineView
     */
    public function setMargin(string $margin = null): self
    {
        $this->margin = $margin;

        return $this;
    }

    /**
     * Returns the margin in percentage.
     *
     * @return string
     */
    public function getMargin(): ?string
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
    public function addLine(LineView $line): self
    {
        $this->lines[] = $line;

        return $this;
    }

    /**
     * Returns the lines.
     *
     * @return LineView[]
     */
    public function getLines(): array
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
    public function setPrivate(bool $private): self
    {
        $this->private = (bool)$private;

        return $this;
    }

    /**
     * Returns whether or not the line is private.
     *
     * @return bool
     */
    public function isPrivate(): bool
    {
        return $this->private;
    }

    /**
     * Returns the source.
     *
     * @return object
     */
    public function getSource(): object
    {
        return $this->source;
    }

    /**
     * Sets the source.
     *
     * @param object $source
     *
     * @return LineView
     */
    public function setSource(object $source): self
    {
        $this->source = $source;

        return $this;
    }
}
