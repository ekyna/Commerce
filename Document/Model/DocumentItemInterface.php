<?php

namespace Ekyna\Component\Commerce\Document\Model;

use Ekyna\Component\Commerce\Pricing\Model\TaxableInterface;

/**
 * Interface DocumentItemInterface
 * @package Ekyna\Component\Commerce\Document\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface DocumentItemInterface extends TaxableInterface
{
    /**
     * Returns the document.
     *
     * @return DocumentInterface|null
     */
    public function getDocument(): ?DocumentInterface;

    /**
     * Sets the document.
     *
     * @param DocumentInterface $document
     *
     * @return DocumentItemInterface
     */
    public function setDocument(DocumentInterface $document = null): DocumentItemInterface;

    /**
     * Returns the designation.
     *
     * @return string|null
     */
    public function getDesignation(): ?string;

    /**
     * Sets the designation.
     *
     * @param string $designation
     *
     * @return DocumentItemInterface
     */
    public function setDesignation(string $designation): DocumentItemInterface;

    /**
     * Returns the description.
     *
     * @return string|null
     */
    public function getDescription(): ?string;

    /**
     * Sets the description.
     *
     * @param string|null $description
     *
     * @return DocumentItemInterface
     */
    public function setDescription(string $description = null): DocumentItemInterface;

    /**
     * Returns the reference.
     *
     * @return string|null
     */
    public function getReference(): ?string;

    /**
     * Sets the reference.
     *
     * @param string $reference
     *
     * @return DocumentItemInterface
     */
    public function setReference(string $reference): DocumentItemInterface;

    /**
     * Returns the unit.
     *
     * @param bool $ati
     *
     * @return float
     */
    public function getUnit(bool $ati = false): float;

    /**
     * Sets the unit.
     *
     * @param float $unit
     *
     * @return DocumentItemInterface
     */
    public function setUnit(float $unit): DocumentItemInterface;

    /**
     * Returns the quantity.
     *
     * @return float
     */
    public function getQuantity(): float;

    /**
     * Sets the quantity.
     *
     * @param float $quantity
     *
     * @return DocumentItemInterface
     */
    public function setQuantity(float $quantity): DocumentItemInterface;

    /**
     * Returns the gross.
     *
     * @param bool $ati
     *
     * @return float
     */
    public function getGross(bool $ati = false): float;

    /**
     * Sets the gross.
     *
     * @param float $gross
     *
     * @return DocumentItemInterface
     */
    public function setGross(float $gross): DocumentItemInterface;

    /**
     * Returns the discount.
     *
     * @param bool $ati
     *
     * @return float
     */
    public function getDiscount(bool $ati = false): float;

    /**
     * Sets the discount.
     *
     * @param float $discount
     *
     * @return DocumentItemInterface
     */
    public function setDiscount(float $discount): DocumentItemInterface;

    /**
     * Returns the discount rates.
     *
     * @param bool $ati
     *
     * @return array
     */
    public function getDiscountRates(bool $ati = false): array;

    /**
     * Sets the discount rates.
     *
     * @param array $rates
     *
     * @return DocumentItemInterface
     */
    public function setDiscountRates(array $rates): DocumentItemInterface;

    /**
     * Returns the base.
     *
     * @param bool $ati
     *
     * @return float
     */
    public function getBase(bool $ati = false): float;

    /**
     * Sets the base.
     *
     * @param float $base
     *
     * @return DocumentItemInterface
     */
    public function setBase(float $base): DocumentItemInterface;

    /**
     * Returns the tax.
     *
     * @return float
     */
    public function getTax(): float;

    /**
     * Sets the tax.
     *
     * @param float $tax
     *
     * @return DocumentItemInterface
     */
    public function setTax(float $tax): DocumentItemInterface;

    /**
     * Returns the tax rates.
     *
     * @return array
     */
    public function getTaxRates(): array;

    /**
     * Sets the tax rates.
     *
     * @param array $rates
     *
     * @return DocumentItemInterface
     */
    public function setTaxRates(array $rates): DocumentItemInterface;

    /**
     * Returns the total.
     *
     * @return float
     */
    public function getTotal(): float;

    /**
     * Sets the total.
     *
     * @param float $total
     *
     * @return DocumentItemInterface
     */
    public function setTotal(float $total): DocumentItemInterface;
}
