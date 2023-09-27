<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Document\Model;

use Decimal\Decimal;
use Ekyna\Component\Commerce\Common\Model as Common;

/**
 * Interface DocumentLineInterface
 * @package Ekyna\Component\Commerce\Document\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface DocumentLineInterface
{
    public function getDocument(): ?DocumentInterface;

    public function setDocument(?DocumentInterface $document): DocumentLineInterface;

    public function getSale(): ?Common\SaleInterface;

    public function getSaleItem(): ?Common\SaleItemInterface;

    public function setSaleItem(?Common\SaleItemInterface $item): DocumentLineInterface;

    public function getSaleAdjustment(): ?Common\SaleAdjustmentInterface;

    public function setSaleAdjustment(?Common\SaleAdjustmentInterface $adjustment): DocumentLineInterface;

    public function getType(): ?string;

    public function setType(?string $type): DocumentLineInterface;

    public function getDesignation(): ?string;

    public function setDesignation(?string $designation): DocumentLineInterface;

    public function getDescription(): ?string;

    public function setDescription(?string $description): DocumentLineInterface;

    public function getIncluded(): ?string;

    public function setIncluded(?string $included): DocumentLineInterface;

    public function getReference(): ?string;

    public function setReference(?string $reference): DocumentLineInterface;

    public function getUnit(bool $ati = false): Decimal;

    public function setUnit(Decimal $price): DocumentLineInterface;

    public function getQuantity(): Decimal;

    public function setQuantity(Decimal $quantity): DocumentLineInterface;

    public function getGross(bool $ati = false): Decimal;

    public function setGross(Decimal $total): DocumentLineInterface;

    public function getDiscount(bool $ati = false): Decimal;

    public function setDiscount(Decimal $total): DocumentLineInterface;

    public function getDiscountRates(): array;

    public function setDiscountRates(array $rates): DocumentLineInterface;

    public function getBase(bool $ati = false): Decimal;

    public function setBase(Decimal $total): DocumentLineInterface;

    public function getTax(): Decimal;

    public function setTax(Decimal $tax): DocumentLineInterface;

    public function getTaxRates(): array;

    public function setTaxRates(array $rates): DocumentLineInterface;

    public function getIncludedDetails(): array;

    public function setIncludedDetails(array $details): DocumentLineInterface;

    public function getTotal(): Decimal;

    public function setTotal(Decimal $total): DocumentLineInterface;
}
