<?php

namespace Ekyna\Component\Commerce\Document\Model;

use Doctrine\Common\Collections\Collection;
use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Resource\Model\LocalizedInterface;

/**
 * Interface DocumentInterface
 * @package Ekyna\Component\Commerce\Document\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface DocumentInterface extends LocalizedInterface
{
    /**
     * Returns the sale.
     *
     * @return SaleInterface
     */
    public function getSale(): ?SaleInterface;

    /**
     * Sets the sale.
     *
     * @param SaleInterface $sale
     *
     * @return $this|DocumentInterface
     */
    public function setSale(SaleInterface $sale = null): DocumentInterface;

    /**
     * Returns the type.
     *
     * @return string
     */
    public function getType(): ?string;

    /**
     * Sets the type.
     *
     * @param string $type
     *
     * @return $this|DocumentInterface
     */
    public function setType(string $type): DocumentInterface;

    /**
     * Returns the currency.
     *
     * @return string
     */
    public function getCurrency(): ?string;

    /**
     * Sets the currency.
     *
     * @param string $currency
     *
     * @return $this|DocumentInterface
     */
    public function setCurrency(string $currency = null): DocumentInterface;

    /**
     * Sets the locale.
     *
     * @param string $locale
     *
     * @return $this|DocumentInterface
     */
    public function setLocale(string $locale = null): DocumentInterface;

    /**
     * Returns the customer data.
     *
     * @return array
     */
    public function getCustomer(): ?array;

    /**
     * Sets the customer data.
     *
     * @param array $data
     *
     * @return $this|DocumentInterface
     */
    public function setCustomer(array $data): DocumentInterface;

    /**
     * Returns the invoice address data.
     *
     * @return array
     */
    public function getInvoiceAddress(): ?array;

    /**
     * Sets the invoice address data.
     *
     * @param array $data
     *
     * @return $this|DocumentInterface
     */
    public function setInvoiceAddress(array $data): DocumentInterface;

    /**
     * Returns the delivery address data.
     *
     * @return array
     */
    public function getDeliveryAddress(): ?array;

    /**
     * Sets the delivery address data.
     *
     * @param array|null $data
     *
     * @return $this|DocumentInterface
     */
    public function setDeliveryAddress(array $data = null): DocumentInterface;

    /**
     * Returns the relay point data.
     *
     * @return array
     */
    public function getRelayPoint(): ?array;

    /**
     * Sets the relay point data.
     *
     * @param array|null $data
     *
     * @return $this|DocumentInterface
     */
    public function setRelayPoint(array $data = null): DocumentInterface;

    /**
     * Returns whether the invoice has at least one line or not.
     *
     * @return bool
     */
    public function hasLines(): bool;

    /**
     * Returns the lines.
     *
     * @return Collection|DocumentLineInterface[]
     */
    public function getLines(): Collection;

    /**
     * Returns the lines with the given type.
     *
     * @param string $type
     *
     * @return array|DocumentLineInterface[]
     */
    public function getLinesByType(string $type): array;

    /**
     * Returns whether the invoice has the line or not.
     *
     * @param DocumentLineInterface $line
     *
     * @return bool
     */
    public function hasLine(DocumentLineInterface $line): bool;

    /**
     * Returns whether the invoice has at least one with the given type.
     *
     * @param string $type
     *
     * @return bool
     */
    public function hasLineByType(string $type): bool;

    /**
     * Adds the line.
     *
     * @param DocumentLineInterface $line
     *
     * @return $this|DocumentInterface
     */
    public function addLine(DocumentLineInterface $line): DocumentInterface;

    /**
     * Removes the line.
     *
     * @param DocumentLineInterface $line
     *
     * @return $this|DocumentInterface
     */
    public function removeLine(DocumentLineInterface $line): DocumentInterface;

    /**
     * Sets the document lines.
     *
     * @param Collection|DocumentLineInterface[] $lines
     *
     * @return $this|DocumentInterface
     */
    public function setLines(Collection $lines): DocumentInterface;

    /**
     * Returns the comment.
     *
     * @return string
     */
    public function getComment(): ?string;

    /**
     * Sets the comment.
     *
     * @param string $comment
     *
     * @return $this|DocumentInterface
     */
    public function setComment(string $comment = null): DocumentInterface;

    /**
     * Returns the description.
     *
     * @return string
     */
    public function getDescription(): ?string;

    /**
     * Sets the description.
     *
     * @param string $description
     *
     * @return $this|DocumentInterface
     */
    public function setDescription(string $description = null): DocumentInterface;

    /**
     * Returns the goods base (after discounts).
     *
     * @param bool $ati
     *
     * @return float
     */
    public function getGoodsBase(bool $ati = false): float;

    /**
     * Sets the goods base (after discounts).
     *
     * @param float $base
     *
     * @return $this|DocumentInterface
     */
    public function setGoodsBase(float $base): DocumentInterface;

    /**
     * Returns the discount base.
     *
     * @param bool $ati
     *
     * @return float
     */
    public function getDiscountBase(bool $ati = false): float;

    /**
     * Sets the discount base.
     *
     * @param float $base
     *
     * @return Document
     */
    public function setDiscountBase(float $base): DocumentInterface;

    /**
     * Returns the shipment base.
     *
     * @param bool $ati
     *
     * @return float
     */
    public function getShipmentBase(bool $ati = false): float;

    /**
     * Sets the shipment base.
     *
     * @param float $base
     *
     * @return $this|DocumentInterface
     */
    public function setShipmentBase(float $base): DocumentInterface;

    /**
     * Returns the taxes total.
     *
     * @return float
     */
    public function getTaxesTotal(): float;

    /**
     * Sets the taxes total.
     *
     * @param float $total
     *
     * @return $this|DocumentInterface
     */
    public function setTaxesTotal(float $total): DocumentInterface;

    /**
     * Returns the taxes details.
     *
     * @return array
     */
    public function getTaxesDetails(): array;

    /**
     * Sets the taxes details.
     *
     * @param array $details
     *
     * @return $this|DocumentInterface
     */
    public function setTaxesDetails(array $details): DocumentInterface;

    /**
     * Returns the grand total (document currency).
     *
     * @return float
     */
    public function getGrandTotal(): float;

    /**
     * Sets the grand total (document currency).
     *
     * @param float $total
     *
     * @return $this|DocumentInterface
     */
    public function setGrandTotal(float $total): DocumentInterface;

    /**
     * Returns the real grand total (default currency).
     *
     * @return float
     */
    public function getRealGrandTotal(): float;

    /**
     * Sets the real grand total (default currency).
     *
     * @param float $amount
     *
     * @return $this|DocumentInterface
     */
    public function setRealGrandTotal(float $amount): DocumentInterface;

    /**
     * Returns whether the document has at least one line discount.
     *
     * @return bool
     */
    public function hasLineDiscount(): bool;

    /**
     * Returns whether the document has multiple taxes.
     *
     * @return bool
     */
    public function hasMultipleTaxes(): bool;

    /**
     * Returns the ati.
     *
     * @return bool
     */
    public function isAti(): bool;
}
