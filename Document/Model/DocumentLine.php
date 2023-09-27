<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Document\Model;

use Decimal\Decimal;
use Ekyna\Component\Commerce\Common\Model as Common;
use Ekyna\Component\Commerce\Common\Util\Money;

/**
 * Class DocumentLine
 * @package Ekyna\Component\Commerce\Common\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class DocumentLine implements DocumentLineInterface
{
    protected ?Document                       $document       = null;
    protected ?string                         $type           = null;
    protected ?string                         $designation    = null;
    protected ?string                         $description    = null;
    protected ?string                         $included       = null;
    protected ?string                         $reference      = null;
    protected Decimal                         $unit;
    protected Decimal                         $quantity;
    protected Decimal                         $gross;
    protected Decimal                         $discount;
    protected array                           $discountRates;
    protected Decimal                         $base;
    protected Decimal                         $tax;
    protected array                           $taxRates;
    protected array                           $includedDetails;
    protected Decimal                         $total;
    protected ?Common\SaleItemInterface       $saleItem       = null;
    protected ?Common\SaleAdjustmentInterface $saleAdjustment = null;

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
        $this->includedDetails = [];
        $this->total = new Decimal(0);
    }

    public function getDocument(): ?DocumentInterface
    {
        return $this->document;
    }

    public function setDocument(?DocumentInterface $document): DocumentLineInterface
    {
        if ($this->document === $document) {
            return $this;
        }

        if ($previous = $this->document) {
            $this->document = null;
            $previous->removeLine($this);
        }

        if ($this->document = $document) {
            $this->document->addLine($this);
        }

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): DocumentLineInterface
    {
        $this->type = $type;

        return $this;
    }

    public function getDesignation(): ?string
    {
        return $this->designation;
    }

    public function setDesignation(?string $designation): DocumentLineInterface
    {
        $this->designation = $designation;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): DocumentLineInterface
    {
        $this->description = $description;

        return $this;
    }

    public function getIncluded(): ?string
    {
        return $this->included;
    }

    public function setIncluded(?string $included): DocumentLineInterface
    {
        $this->included = $included;

        return $this;
    }

    public function getReference(): ?string
    {
        return $this->reference;
    }

    public function setReference(?string $reference): DocumentLineInterface
    {
        $this->reference = $reference;

        return $this;
    }

    public function getUnit(bool $ati = false): Decimal
    {
        return $ati ? $this->ati($this->unit) : $this->unit;
    }

    public function setUnit(Decimal $price): DocumentLineInterface
    {
        $this->unit = $price;

        return $this;
    }

    public function getQuantity(): Decimal
    {
        return $this->quantity;
    }

    public function setQuantity(Decimal $quantity): DocumentLineInterface
    {
        $this->quantity = $quantity;

        return $this;
    }

    public function getGross(bool $ati = false): Decimal
    {
        return $ati ? $this->ati($this->gross) : $this->gross;
    }

    public function setGross(Decimal $total): DocumentLineInterface
    {
        $this->gross = $total;

        return $this;
    }

    public function getDiscount(bool $ati = false): Decimal
    {
        return $ati ? $this->ati($this->discount) : $this->discount;
    }

    public function setDiscount(Decimal $total): DocumentLineInterface
    {
        $this->discount = $total;

        return $this;
    }

    public function getDiscountRates(): array
    {
        return $this->discountRates;
    }

    public function setDiscountRates(array $rates): DocumentLineInterface
    {
        $this->discountRates = $rates;

        return $this;
    }

    public function getBase(bool $ati = false): Decimal
    {
        return $ati ? $this->ati($this->base) : $this->base;
    }

    public function setBase(Decimal $total): DocumentLineInterface
    {
        $this->base = $total;

        return $this;
    }

    public function getTax(): Decimal
    {
        return $this->tax;
    }

    public function setTax(Decimal $tax): DocumentLineInterface
    {
        $this->tax = $tax;

        return $this;
    }

    public function getTaxRates(): array
    {
        return $this->taxRates;
    }

    public function setTaxRates(array $rates): DocumentLineInterface
    {
        $this->taxRates = $rates;

        return $this;
    }

    public function getIncludedDetails(): array
    {
        return $this->includedDetails;
    }

    public function setIncludedDetails(array $details): DocumentLineInterface
    {
        $this->includedDetails = $details;

        return $this;
    }

    public function getTotal(): Decimal
    {
        return $this->total;
    }

    public function setTotal(Decimal $total): DocumentLineInterface
    {
        $this->total = $total;

        return $this;
    }

    public function getSale(): ?Common\SaleInterface
    {
        if (null === $document = $this->getDocument()) {
            return null;
        }

        return $document->getSale();
    }

    public function getSaleItem(): ?Common\SaleItemInterface
    {
        return $this->saleItem;
    }

    public function setSaleItem(?Common\SaleItemInterface $item): DocumentLineInterface
    {
        $this->saleItem = $item;

        return $this;
    }

    public function getSaleAdjustment(): ?Common\SaleAdjustmentInterface
    {
        return $this->saleAdjustment;
    }

    public function setSaleAdjustment(?Common\SaleAdjustmentInterface $adjustment): DocumentLineInterface
    {
        $this->saleAdjustment = $adjustment;

        return $this;
    }

    /**
     * Adds the taxes to the given amount.
     */
    private function ati(Decimal $amount): Decimal
    {
        $result = $amount;

        foreach ($this->taxRates as $rate) {
            $result += $amount * $rate / 100;
        }

        return Money::round($result, $this->getDocument()->getCurrency());
    }
}
