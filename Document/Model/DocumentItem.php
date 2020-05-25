<?php

namespace Ekyna\Component\Commerce\Document\Model;

use Ekyna\Component\Commerce\Common\Util\Money;
use Ekyna\Component\Commerce\Pricing\Model\TaxableTrait;

/**
 * Class DocumentItem
 * @package Ekyna\Component\Commerce\Document\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class DocumentItem implements DocumentItemInterface
{
    use TaxableTrait;

    /**
     * @var DocumentInterface
     */
    protected $document;

    /**
     * @var string
     */
    protected $designation;

    /**
     * @var string
     */
    protected $description;

    /**
     * @var string
     */
    protected $reference;

    /**
     * @var float
     */
    protected $unit;

    /**
     * @var float
     */
    protected $quantity;

    /**
     * @var float
     */
    protected $gross;

    /**
     * @var float
     */
    protected $discount;

    /**
     * @var array
     */
    protected $discountRates;

    /**
     * @var float
     */
    protected $base;

    /**
     * @var float
     */
    protected $tax;

    /**
     * @var array
     */
    protected $taxRates;

    /**
     * @var float
     */
    protected $total;


    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->unit = 0;
        $this->quantity = 0;
        $this->gross = 0;
        $this->discount = 0;
        $this->discountRates = [];
        $this->base = 0;
        $this->tax = 0;
        $this->taxRates = [];
        $this->total = 0;
    }

    /**
     * {@inheritDoc}
     */
    public function getDocument(): ?DocumentInterface
    {
        return $this->document;
    }

    /**
     * {@inheritDoc}
     */
    public function setDocument(DocumentInterface $document = null): DocumentItemInterface
    {
        if ($this->document !== $document) {
            if ($previous = $this->document) {
                $this->document = null;
                $previous->removeItem($this);
            }

            if ($this->document = $document) {
                $this->document->addItem($this);
            }
        }

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getDesignation(): ?string
    {
        return $this->designation;
    }

    /**
     * {@inheritDoc}
     */
    public function setDesignation(string $designation): DocumentItemInterface
    {
        $this->designation = $designation;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * {@inheritDoc}
     */
    public function setDescription(string $description = null): DocumentItemInterface
    {
        $this->description = $description;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getReference(): ?string
    {
        return $this->reference;
    }

    /**
     * {@inheritDoc}
     */
    public function setReference(string $reference): DocumentItemInterface
    {
        $this->reference = $reference;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getUnit(bool $ati = false): float
    {
        return $ati ? $this->ati($this->unit) : $this->unit;
    }

    /**
     * {@inheritDoc}
     */
    public function setUnit(float $unit): DocumentItemInterface
    {
        $this->unit = $unit;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getQuantity(): float
    {
        return $this->quantity;
    }

    /**
     * {@inheritDoc}
     */
    public function setQuantity(float $quantity): DocumentItemInterface
    {
        $this->quantity = $quantity;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getGross(bool $ati = false): float
    {
        return $ati ? $this->ati($this->gross) : $this->gross;
    }

    /**
     * {@inheritDoc}
     */
    public function setGross(float $gross): DocumentItemInterface
    {
        $this->gross = $gross;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getDiscount(bool $ati = false): float
    {
        return $ati ? $this->ati($this->discount) : $this->discount;
    }

    /**
     * {@inheritDoc}
     */
    public function setDiscount(float $discount): DocumentItemInterface
    {
        $this->discount = $discount;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getDiscountRates(bool $ati = false): array
    {
        return $this->discountRates;
    }

    /**
     * {@inheritDoc}
     */
    public function setDiscountRates(array $rates): DocumentItemInterface
    {
        $this->discountRates = $rates;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getBase(bool $ati = false): float
    {
        return $ati ? $this->ati($this->base) : $this->base;
    }

    /**
     * {@inheritDoc}
     */
    public function setBase(float $base): DocumentItemInterface
    {
        $this->base = $base;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getTax(): float
    {
        return $this->tax;
    }

    /**
     * {@inheritDoc}
     */
    public function setTax(float $tax): DocumentItemInterface
    {
        $this->tax = $tax;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getTaxRates(): array
    {
        return $this->taxRates;
    }

    /**
     * {@inheritDoc}
     */
    public function setTaxRates(array $rates): DocumentItemInterface
    {
        $this->taxRates = $rates;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getTotal(): float
    {
        return $this->total;
    }

    /**
     * {@inheritDoc}
     */
    public function setTotal(float $total): DocumentItemInterface
    {
        $this->total = $total;

        return $this;
    }

    /**
     * Adds the taxes to the given amount.
     *
     * @param float $amount
     *
     * @return float
     */
    private function ati(float $amount)
    {
        $result = $amount;

        foreach ($this->taxRates as $rate) {
            $result += $amount * $rate / 100;
        }

        return Money::round($result, $this->getDocument()->getCurrency());
    }
}
