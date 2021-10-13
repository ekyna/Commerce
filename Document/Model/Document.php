<?php

namespace Ekyna\Component\Commerce\Document\Model;

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
    /**
     * @var string
     */
    protected $type;

    /**
     * @var string
     */
    protected $currency;

    /**
     * @var string
     */
    protected $locale;

    /**
     * @var array
     */
    protected $customer;

    /**
     * @var array
     */
    protected $invoiceAddress;

    /**
     * @var array
     */
    protected $deliveryAddress;

    /**
     * @var array
     */
    protected $relayPoint;

    /**
     * @var Collection|DocumentLineInterface[]
     */
    protected $lines;

    /**
     * @var Collection|DocumentItemInterface[]
     */
    protected $items;

    /**
     * @var string
     */
    protected $comment;

    /**
     * @var string
     */
    protected $description;

    /**
     * @var float
     */
    protected $goodsBase;

    /**
     * @var float
     */
    protected $discountBase;

    /**
     * @var float
     */
    protected $shipmentBase;

    /**
     * @var float
     */
    protected $taxesTotal;

    /**
     * @var array
     */
    protected $taxesDetails;

    /**
     * The grand total in document currency.
     *
     * @var float
     */
    protected $grandTotal;

    /**
     * The grand total in default currency.
     *
     * @var float
     */
    protected $realGrandTotal;

    /**
     * @var TaxRuleInterface
     */
    protected $taxRule;

    /**
     * @var SaleInterface
     */
    protected $sale;


    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->goodsBase = 0;
        $this->discountBase = 0;
        $this->shipmentBase = 0;
        $this->taxesTotal = 0;
        $this->taxesDetails = [];
        $this->grandTotal = 0;
        $this->realGrandTotal = 0;
        $this->lines = new ArrayCollection();
        $this->items = new ArrayCollection();
    }

    /**
     * @inheritdoc
     */
    public function getType(): ?string
    {
        return $this->type;
    }

    /**
     * @inheritdoc
     */
    public function setType(string $type): DocumentInterface
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getCurrency(): ?string
    {
        if (!empty($this->currency)) {
            return $this->currency;
        }

        return $this->getSale()->getCurrency()->getCode();
    }

    /**
     * @inheritdoc
     */
    public function setCurrency(string $currency = null): DocumentInterface
    {
        $this->currency = $currency;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getLocale(): string
    {
        if (!empty($this->locale)) {
            return $this->locale;
        }

        return $this->getSale()->getLocale();
    }

    /**
     * @inheritDoc
     */
    public function setLocale(string $locale = null): DocumentInterface
    {
        $this->locale = $locale;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getCustomer(): ?array
    {
        return $this->customer;
    }

    /**
     * @inheritdoc
     */
    public function setCustomer(array $data): DocumentInterface
    {
        $this->customer = $data;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getInvoiceAddress(): ?array
    {
        return $this->invoiceAddress;
    }

    /**
     * @inheritdoc
     */
    public function setInvoiceAddress(array $data): DocumentInterface
    {
        $this->invoiceAddress = $data;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getDeliveryAddress():? array
    {
        return $this->deliveryAddress;
    }

    /**
     * @inheritdoc
     */
    public function setDeliveryAddress(array $data = null): DocumentInterface
    {
        $this->deliveryAddress = $data;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getRelayPoint(): ?array
    {
        return $this->relayPoint;
    }

    /**
     * @inheritdoc
     */
    public function setRelayPoint(array $data = null): DocumentInterface
    {
        $this->relayPoint = $data;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function hasLines(): bool
    {
        return 0 < $this->lines->count();
    }

    /**
     * @inheritdoc
     */
    public function getLines(): Collection
    {
        return $this->lines;
    }

    /**
     * @inheritdoc
     */
    public function getLinesByType(string $type): array
    {
        if (!DocumentLineTypes::isValidType($type)) {
            throw new InvalidArgumentException("Invalid document line type.");
        }

        $lines = [];

        foreach ($this->getLines() as $line) {
            if ($line->getType() === $type) {
                $lines[] = $line;
            }
        }

        return $lines;
    }

    /**
     * @inheritdoc
     */
    public function hasLine(DocumentLineInterface $line): bool
    {
        return $this->lines->contains($line);
    }

    /**
     * @inheritdoc
     */
    public function hasLineByType(string $type): bool
    {
        if (!DocumentLineTypes::isValidType($type)) {
            throw new InvalidArgumentException("Invalid document line type.");
        }

        foreach ($this->getLines() as $line) {
            if ($line->getType() === $type) {
                return true;
            }
        }

        return false;
    }

    /**
     * @inheritdoc
     */
    public function addLine(DocumentLineInterface $line): DocumentInterface
    {
        if (!$this->hasLine($line)) {
            $this->lines->add($line);
            $line->setDocument($this);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function removeLine(DocumentLineInterface $line): DocumentInterface
    {
        if ($this->hasLine($line)) {
            $this->lines->removeElement($line);
            $line->setDocument(null);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
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

    /**
     * @inheritdoc
     */
    public function hasItems(): bool
    {
        return 0 < $this->items->count();
    }

    /**
     * @inheritdoc
     */
    public function hasItem(DocumentItemInterface $item): bool
    {
        return $this->items->contains($item);
    }

    /**
     * @inheritdoc
     */
    public function addItem(DocumentItemInterface $item): DocumentInterface
    {
        if (!$this->hasItem($item)) {
            $this->items->add($item);
            $item->setDocument($this);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function removeItem(DocumentItemInterface$item): DocumentInterface
    {
        if ($this->hasItem($item)) {
            $this->items->removeElement($item);
            $item->setDocument(null);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
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

    /**
     * @inheritdoc
     */
    public function getItems(): Collection
    {
        return $this->items;
    }

    /**
     * @inheritdoc
     */
    public function getComment(): ?string
    {
        return $this->comment;
    }

    /**
     * @inheritdoc
     */
    public function setComment(string $comment = null): DocumentInterface
    {
        $this->comment = $comment;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @inheritdoc
     */
    public function setDescription(string $description = null): DocumentInterface
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getGoodsBase(bool $ati = false): float
    {
        return $ati ? $this->ati($this->goodsBase) : $this->goodsBase;
    }

    /**
     * @inheritdoc
     */
    public function setGoodsBase(float $base): DocumentInterface
    {
        $this->goodsBase = $base;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getDiscountBase(bool $ati = false): float
    {
        return $ati ? $this->ati($this->discountBase) : $this->discountBase;
    }

    /**
     * @inheritdoc
     */
    public function setDiscountBase(float $base): DocumentInterface
    {
        $this->discountBase = $base;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getShipmentBase(bool $ati = false): float
    {
        return $ati ? $this->ati($this->shipmentBase) : $this->shipmentBase;
    }

    /**
     * @inheritdoc
     */
    public function setShipmentBase(float $base): DocumentInterface
    {
        $this->shipmentBase = $base;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getTaxesTotal(): float
    {
        return $this->taxesTotal;
    }

    /**
     * @inheritdoc
     */
    public function setTaxesTotal(float $total): DocumentInterface
    {
        $this->taxesTotal = $total;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getTaxesDetails(): array
    {
        return $this->taxesDetails;
    }

    /**
     * @inheritdoc
     */
    public function setTaxesDetails(array $details): DocumentInterface
    {
        $this->taxesDetails = $details;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getGrandTotal(): float
    {
        return $this->grandTotal;
    }

    /**
     * @inheritdoc
     */
    public function setGrandTotal(float $total): DocumentInterface
    {
        $this->grandTotal = $total;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getRealGrandTotal(): float
    {
        return $this->realGrandTotal;
    }

    /**
     * @inheritDoc
     */
    public function setRealGrandTotal(float $amount): DocumentInterface
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

    /**
     * @inheritdoc
     */
    public function getSale(): ?SaleInterface
    {
        return $this->sale;
    }

    /**
     * @inheritdoc
     */
    public function setSale(SaleInterface $sale = null): DocumentInterface
    {
        $this->sale = $sale;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function hasLineDiscount(): bool
    {
        foreach ($this->lines as $line) {
            if (0 != $line->getDiscount()) {
                return true;
            }
        }

        return false;
    }

    /**
     * @inheritdoc
     */
    public function hasMultipleTaxes(): bool
    {
        return 1 < count($this->taxesDetails);
    }

    /**
     * Adds the taxes to the given amount.
     *
     * @param float $amount
     *
     * @return float
     */
    private function ati(float $amount): float
    {
        $result = $amount;

        foreach ($this->taxesDetails as $tax) {
            $result += $amount * $tax['rate'] / 100;
        }

        return Money::round($result, $this->currency);
    }

    /**
     * @inheritdoc
     */
    public function isAti(): bool
    {
        return $this->getSale()->isAtiDisplayMode();
    }
}
