<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Document\Model;

use Decimal\Decimal;
use Ekyna\Component\Commerce\Pricing\Model\TaxableInterface;

/**
 * Interface DocumentItemInterface
 * @package Ekyna\Component\Commerce\Document\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface DocumentItemInterface extends TaxableInterface
{
    public function getDocument(): ?DocumentInterface;

    public function setDocument(?DocumentInterface $document): DocumentItemInterface;

    public function getDesignation(): ?string;

    public function setDesignation(?string $designation): DocumentItemInterface;

    public function getDescription(): ?string;

    public function setDescription(?string $description): DocumentItemInterface;

    public function getReference(): ?string;

    public function setReference(?string $reference): DocumentItemInterface;

    public function getUnit(bool $ati = false): Decimal;

    public function setUnit(Decimal $unit): DocumentItemInterface;

    public function getQuantity(): Decimal;

    public function setQuantity(Decimal $quantity): DocumentItemInterface;

    public function getGross(bool $ati = false): Decimal;

    public function setGross(Decimal $gross): DocumentItemInterface;

    public function getDiscount(bool $ati = false): Decimal;

    public function setDiscount(Decimal $discount): DocumentItemInterface;

    public function getDiscountRates(bool $ati = false): array;

    public function setDiscountRates(array $rates): DocumentItemInterface;

    public function getBase(bool $ati = false): Decimal;

    public function setBase(Decimal $base): DocumentItemInterface;

    public function getTax(): Decimal;

    public function setTax(Decimal $tax): DocumentItemInterface;

    public function getTaxRates(): array;

    public function setTaxRates(array $rates): DocumentItemInterface;

    public function getTotal(): Decimal;

    public function setTotal(Decimal $total): DocumentItemInterface;
}
