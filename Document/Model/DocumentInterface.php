<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Document\Model;

use Decimal\Decimal;
use Doctrine\Common\Collections\Collection;
use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Pricing\Model\TaxRuleInterface;
use Ekyna\Component\Resource\Model\LocalizedInterface;

/**
 * Interface DocumentInterface
 * @package Ekyna\Component\Commerce\Document\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface DocumentInterface extends LocalizedInterface
{
    public function getSale(): ?SaleInterface;

    public function setSale(?SaleInterface $sale): DocumentInterface;

    public function getType(): ?string;

    public function setType(string $type): DocumentInterface;

    public function getCurrency(): ?string;

    public function setCurrency(?string $currency): DocumentInterface;

    public function setLocale(?string $locale): DocumentInterface;

    public function getCustomer(): ?array;

    public function setCustomer(?array $data): DocumentInterface;

    public function getInvoiceAddress(): ?array;

    public function setInvoiceAddress(?array $data): DocumentInterface;

    public function getDeliveryAddress(): ?array;

    public function setDeliveryAddress(?array $data): DocumentInterface;

    public function getRelayPoint(): ?array;

    public function setRelayPoint(?array $data): DocumentInterface;

    /**
     * Returns whether the invoice has at least one line or not.
     *
     * @return bool
     */
    public function hasLines(): bool;

    /**
     * @return Collection<int, DocumentLineInterface>
     */
    public function getLines(): Collection;

    /**
     * Returns the lines with the given type.
     *
     * @return array<DocumentLineInterface>
     */
    public function getLinesByType(string $type): array;

    /**
     * Returns whether the invoice has the line or not.
     */
    public function hasLine(DocumentLineInterface $line): bool;

    /**
     * Returns whether the invoice has at least one with the given type.
     */
    public function hasLineByType(string $type): bool;

    /**
     * @return $this|DocumentInterface
     */
    public function addLine(DocumentLineInterface $line): DocumentInterface;

    /**
     * @return $this|DocumentInterface
     */
    public function removeLine(DocumentLineInterface $line): DocumentInterface;

    /**
     * @param Collection<int, DocumentLineInterface> $lines
     *
     * @return $this|DocumentInterface
     */
    public function setLines(Collection $lines): DocumentInterface;

    /**
     * Returns whether the document has items.
     */
    public function hasItems(): bool;

    /**
     * Returns whether the document has the given item.
     */
    public function hasItem(DocumentItemInterface $item): bool;

    /**
     * @return $this|DocumentInterface
     */
    public function addItem(DocumentItemInterface $item): DocumentInterface;

    /**
     * @return $this|DocumentInterface
     */
    public function removeItem(DocumentItemInterface$item): DocumentInterface;

    /**
     * @return $this|DocumentInterface
     */
    public function setItems(Collection $items): DocumentInterface;

    /**
     * @return Collection<int, DocumentItemInterface>
     */
    public function getItems(): Collection;

    public function getComment(): ?string;

    /**
     * @return $this|DocumentInterface
     */
    public function setComment(?string $comment): DocumentInterface;

    public function getDescription(): ?string;

    /**
     * @return $this|DocumentInterface
     */
    public function setDescription(?string $description): DocumentInterface;

    /**
     * Returns the goods base (after discounts).
     */
    public function getGoodsBase(bool $ati = false): Decimal;

    /**
     * Sets the goods base (after discounts).
     *
     * @return $this|DocumentInterface
     */
    public function setGoodsBase(Decimal $base): DocumentInterface;

    public function getDiscountBase(bool $ati = false): Decimal;

    public function setDiscountBase(Decimal $base): DocumentInterface;

    public function getShipmentBase(bool $ati = false): Decimal;

    public function setShipmentBase(Decimal $base): DocumentInterface;

    public function getTaxesTotal(): Decimal;

    /**
     * @return $this|DocumentInterface
     */
    public function setTaxesTotal(Decimal $total): DocumentInterface;

    public function getTaxesDetails(): array;

    /**
     * @return $this|DocumentInterface
     */
    public function setTaxesDetails(array $details): DocumentInterface;

    public function getIncludedDetails(): array;

    /**
     * @return $this|DocumentInterface
     */
    public function setIncludedDetails(array $includes): DocumentInterface;

    /**
     * Returns the grand total (document currency).
     */
    public function getGrandTotal(): Decimal;

    /**
     * Sets the grand total (document currency).
     *
     * @return $this|DocumentInterface
     */
    public function setGrandTotal(Decimal $total): DocumentInterface;

    /**
     * Returns the real grand total (default currency).
     */
    public function getRealGrandTotal(): Decimal;

    /**
     * Sets the real grand total (default currency).
     *
     * @return $this|DocumentInterface
     */
    public function setRealGrandTotal(Decimal $amount): DocumentInterface;

    public function getTaxRule(): ?TaxRuleInterface;

    public function setTaxRule(?TaxRuleInterface $taxRule): DocumentInterface;

    /**
     * Returns whether the document has at least one line discount.
     */
    public function hasLineDiscount(): bool;

    /**
     * Returns whether the document has multiple taxes.
     */
    public function hasMultipleTaxes(): bool;

    /**
     * Returns the ati.
     */
    public function isAti(): bool;
}
