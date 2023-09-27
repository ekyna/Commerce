<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Document\Model;

use Decimal\Decimal;
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

    protected ?DocumentInterface $document;
    protected ?string            $designation = null;
    protected ?string            $description = null;
    protected ?string            $included    = null;
    protected ?string            $reference   = null;
    protected Decimal            $unit;
    protected Decimal            $quantity;
    protected Decimal            $gross;
    protected Decimal            $discount;
    protected array              $discountRates;
    protected Decimal            $base;
    protected Decimal            $tax;
    protected array              $taxRates;
    protected Decimal            $total;

    public function __construct()
    {
        $this->unit = new Decimal(0);
        $this->quantity = new Decimal(0);
        $this->gross = new Decimal(0);
        $this->discount = new Decimal(0);
        $this->discountRates = [];
        $this->base = new Decimal(0);
        $this->tax = new Decimal(0);
        $this->taxRates = [];
        $this->total = new Decimal(0);
    }

    public function getDocument(): ?DocumentInterface
    {
        return $this->document;
    }

    public function setDocument(?DocumentInterface $document): DocumentItemInterface
    {
        if ($this->document === $document) {
            return $this;
        }

        if ($previous = $this->document) {
            $this->document = null;
            $previous->removeItem($this);
        }

        if ($this->document = $document) {
            $this->document->addItem($this);
        }

        return $this;
    }

    public function getDesignation(): ?string
    {
        return $this->designation;
    }

    public function setDesignation(?string $designation): DocumentItemInterface
    {
        $this->designation = $designation;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): DocumentItemInterface
    {
        $this->description = $description;

        return $this;
    }

    public function getIncluded(): ?string
    {
        return $this->included;
    }

    public function setIncluded(?string $included): DocumentItemInterface
    {
        $this->included = $included;

        return $this;
    }

    public function getReference(): ?string
    {
        return $this->reference;
    }

    public function setReference(?string $reference): DocumentItemInterface
    {
        $this->reference = $reference;

        return $this;
    }

    public function getUnit(bool $ati = false): Decimal
    {
        return $ati ? $this->ati($this->unit) : $this->unit;
    }

    public function setUnit(Decimal $unit): DocumentItemInterface
    {
        $this->unit = $unit;

        return $this;
    }

    public function getQuantity(): Decimal
    {
        return $this->quantity;
    }

    public function setQuantity(Decimal $quantity): DocumentItemInterface
    {
        $this->quantity = $quantity;

        return $this;
    }

    public function getGross(bool $ati = false): Decimal
    {
        return $ati ? $this->ati($this->gross) : $this->gross;
    }

    public function setGross(Decimal $gross): DocumentItemInterface
    {
        $this->gross = $gross;

        return $this;
    }

    public function getDiscount(bool $ati = false): Decimal
    {
        return $ati ? $this->ati($this->discount) : $this->discount;
    }

    public function setDiscount(Decimal $discount): DocumentItemInterface
    {
        $this->discount = $discount;

        return $this;
    }

    public function getDiscountRates(bool $ati = false): array
    {
        return $this->discountRates;
    }

    public function setDiscountRates(array $rates): DocumentItemInterface
    {
        $this->discountRates = $rates;

        return $this;
    }

    public function getBase(bool $ati = false): Decimal
    {
        return $ati ? $this->ati($this->base) : $this->base;
    }

    public function setBase(Decimal $base): DocumentItemInterface
    {
        $this->base = $base;

        return $this;
    }

    public function getTax(): Decimal
    {
        return $this->tax;
    }

    public function setTax(Decimal $tax): DocumentItemInterface
    {
        $this->tax = $tax;

        return $this;
    }

    public function getTaxRates(): array
    {
        return $this->taxRates;
    }

    public function setTaxRates(array $rates): DocumentItemInterface
    {
        $this->taxRates = $rates;

        return $this;
    }

    public function getTotal(): Decimal
    {
        return $this->total;
    }

    public function setTotal(Decimal $total): DocumentItemInterface
    {
        $this->total = $total;

        return $this;
    }

    private function ati(Decimal $amount): Decimal
    {
        $result = $amount;

        foreach ($this->taxRates as $rate) {
            $result += $amount * $rate / 100;
        }

        return Money::round($result, $this->getDocument()->getCurrency());
    }
}
