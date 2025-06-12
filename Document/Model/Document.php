<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Document\Model;

use Decimal\Decimal;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Common\Util\Money;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Pricing\Model\TaxRuleInterface;

/**
 * Class Document
 * @package Ekyna\Component\Commerce\Common\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class Document implements DocumentInterface
{
    protected ?string $type               = null;
    protected ?string $currency           = null;
    protected ?string $locale             = null;
    protected ?array  $customer           = null;
    protected ?array  $invoiceAddress     = null;
    protected ?array  $deliveryAddress    = null;
    protected ?array  $destinationAddress = null;
    protected ?array  $relayPoint         = null;
    protected ?string $incoterm           = null;
    protected ?string $comment            = null;
    protected ?string $description        = null;
    protected Decimal $goodsBase;
    protected Decimal $discountBase;
    protected Decimal $shipmentBase;
    protected Decimal $taxesTotal;
    protected array   $taxesDetails;
    protected array   $includedDetails;
    /** The grand total in document currency. */
    protected Decimal $grandTotal;
    /** The grand total in default currency. */
    protected Decimal $realGrandTotal;

    protected ?SaleInterface    $sale    = null;
    protected ?TaxRuleInterface $taxRule = null;
    /** @var Collection<int, DocumentLineInterface> */
    protected Collection $lines;
    /** @var Collection<int, DocumentItemInterface> */
    protected Collection $items;

    public function __construct()
    {
        $this->goodsBase = new Decimal(0);
        $this->discountBase = new Decimal(0);
        $this->shipmentBase = new Decimal(0);
        $this->taxesTotal = new Decimal(0);
        $this->taxesDetails = [];
        $this->includedDetails = [];
        $this->grandTotal = new Decimal(0);
        $this->realGrandTotal = new Decimal(0);
        $this->lines = new ArrayCollection();
        $this->items = new ArrayCollection();
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): DocumentInterface
    {
        $this->type = $type;

        return $this;
    }

    public function getCurrency(): ?string
    {
        if (!empty($this->currency)) {
            return $this->currency;
        }

        return $this->getSale()->getCurrency()->getCode();
    }

    public function setCurrency(?string $currency): DocumentInterface
    {
        $this->currency = $currency;

        return $this;
    }

    public function getLocale(): string
    {
        if (!empty($this->locale)) {
            return $this->locale;
        }

        return $this->getSale()->getLocale();
    }

    public function setLocale(?string $locale): DocumentInterface
    {
        $this->locale = $locale;

        return $this;
    }

    public function getCustomer(): ?array
    {
        return $this->customer;
    }

    public function setCustomer(?array $data): DocumentInterface
    {
        $this->customer = $data;

        return $this;
    }

    public function getInvoiceAddress(): ?array
    {
        return $this->invoiceAddress;
    }

    public function setInvoiceAddress(?array $data): DocumentInterface
    {
        $this->invoiceAddress = $data;

        return $this;
    }

    public function getDeliveryAddress(): ?array
    {
        return $this->deliveryAddress;
    }

    public function setDeliveryAddress(?array $data): DocumentInterface
    {
        $this->deliveryAddress = $data;

        return $this;
    }

    public function getDestinationAddress(): ?array
    {
        return $this->destinationAddress;
    }

    public function setDestinationAddress(?array $data): DocumentInterface
    {
        $this->destinationAddress = $data;

        return $this;
    }

    public function getRelayPoint(): ?array
    {
        return $this->relayPoint;
    }

    public function setRelayPoint(?array $data): DocumentInterface
    {
        $this->relayPoint = $data;

        return $this;
    }

    public function getIncoterm(): ?string
    {
        return $this->incoterm;
    }

    public function setIncoterm(?string $incoterm): DocumentInterface
    {
        $this->incoterm = $incoterm;

        return $this;
    }

    public function hasLines(): bool
    {
        return 0 < $this->lines->count();
    }

    public function getLines(): Collection
    {
        return $this->lines;
    }

    public function getLinesByType(string $type): array
    {
        if (!DocumentLineTypes::isValidType($type)) {
            throw new InvalidArgumentException('Invalid document line type.');
        }

        $lines = [];

        foreach ($this->getLines() as $line) {
            if ($line->getType() === $type) {
                $lines[] = $line;
            }
        }

        return $lines;
    }

    public function hasLine(DocumentLineInterface $line): bool
    {
        return $this->lines->contains($line);
    }

    public function hasLineByType(string $type): bool
    {
        if (!DocumentLineTypes::isValidType($type)) {
            throw new InvalidArgumentException('Invalid document line type.');
        }

        foreach ($this->getLines() as $line) {
            if ($line->getType() === $type) {
                return true;
            }
        }

        return false;
    }

    public function addLine(DocumentLineInterface $line): DocumentInterface
    {
        if (!$this->hasLine($line)) {
            $this->lines->add($line);
            $line->setDocument($this);
        }

        return $this;
    }

    public function removeLine(DocumentLineInterface $line): DocumentInterface
    {
        if ($this->hasLine($line)) {
            $this->lines->removeElement($line);
            $line->setDocument(null);
        }

        return $this;
    }

    public function setLines(Collection $lines): DocumentInterface
    {
        foreach ($this->lines as $line) {
            if (!$lines->contains($line)) {
                $this->removeLine($line);
            }
        }

        $this->lines = new ArrayCollection();

        foreach ($lines as $line) {
            $this->addLine($line);
        }

        return $this;
    }

    public function hasItems(): bool
    {
        return 0 < $this->items->count();
    }

    public function hasItem(DocumentItemInterface $item): bool
    {
        return $this->items->contains($item);
    }

    public function addItem(DocumentItemInterface $item): DocumentInterface
    {
        if (!$this->hasItem($item)) {
            $this->items->add($item);
            $item->setDocument($this);
        }

        return $this;
    }

    public function removeItem(DocumentItemInterface $item): DocumentInterface
    {
        if ($this->hasItem($item)) {
            $this->items->removeElement($item);
            $item->setDocument(null);
        }

        return $this;
    }

    public function setItems(Collection $items): DocumentInterface
    {
        foreach ($this->items as $item) {
            if (!$items->contains($item)) {
                $this->removeItem($item);
            }
        }

        $this->items = new ArrayCollection();

        foreach ($items as $item) {
            $this->addItem($item);
        }

        return $this;
    }

    public function getItems(): Collection
    {
        return $this->items;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(?string $comment): DocumentInterface
    {
        $this->comment = $comment;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): DocumentInterface
    {
        $this->description = $description;

        return $this;
    }

    public function getGoodsBase(bool $ati = false): Decimal
    {
        return $ati ? $this->ati($this->goodsBase) : $this->goodsBase;
    }

    public function setGoodsBase(Decimal $base): DocumentInterface
    {
        $this->goodsBase = $base;

        return $this;
    }

    public function getDiscountBase(bool $ati = false): Decimal
    {
        return $ati ? $this->ati($this->discountBase) : $this->discountBase;
    }

    public function setDiscountBase(Decimal $base): DocumentInterface
    {
        $this->discountBase = $base;

        return $this;
    }

    public function getShipmentBase(bool $ati = false): Decimal
    {
        return $ati ? $this->ati($this->shipmentBase) : $this->shipmentBase;
    }

    public function setShipmentBase(Decimal $base): DocumentInterface
    {
        $this->shipmentBase = $base;

        return $this;
    }

    public function getTaxesTotal(): Decimal
    {
        return $this->taxesTotal;
    }

    public function setTaxesTotal(Decimal $total): DocumentInterface
    {
        $this->taxesTotal = $total;

        return $this;
    }

    public function getTaxesDetails(): array
    {
        return $this->taxesDetails;
    }

    public function setTaxesDetails(array $details): DocumentInterface
    {
        $this->taxesDetails = $details;

        return $this;
    }

    public function getIncludedDetails(): array
    {
        return $this->includedDetails;
    }

    public function setIncludedDetails(array $details): DocumentInterface
    {
        $this->includedDetails = $details;

        return $this;
    }

    public function getGrandTotal(): Decimal
    {
        return $this->grandTotal;
    }

    public function setGrandTotal(Decimal $total): DocumentInterface
    {
        $this->grandTotal = $total;

        return $this;
    }

    public function getRealGrandTotal(): Decimal
    {
        return $this->realGrandTotal;
    }

    public function setRealGrandTotal(Decimal $amount): DocumentInterface
    {
        $this->realGrandTotal = $amount;

        return $this;
    }

    public function getTaxRule(): ?TaxRuleInterface
    {
        return $this->taxRule;
    }

    public function setTaxRule(?TaxRuleInterface $taxRule): DocumentInterface
    {
        $this->taxRule = $taxRule;

        return $this;
    }

    public function getSale(): ?SaleInterface
    {
        return $this->sale;
    }

    public function setSale(?SaleInterface $sale): DocumentInterface
    {
        $this->sale = $sale;

        return $this;
    }

    public function hasLineDiscount(): bool
    {
        foreach ($this->lines as $line) {
            if (!$line->getDiscount()->isZero()) {
                return true;
            }
        }

        return false;
    }

    public function hasMultipleTaxes(): bool
    {
        return 1 < count($this->taxesDetails);
    }

    /**
     * Adds the taxes to the given amount.
     */
    private function ati(Decimal $amount): Decimal
    {
        $result = $amount;

        foreach ($this->taxesDetails as $tax) {
            $result += $amount * $tax['rate'] / 100;
        }

        return Money::round($result, $this->currency);
    }

    public function isAti(): bool
    {
        return $this->getSale()->isAtiDisplayMode();
    }
}
