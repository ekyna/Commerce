<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Pricing\Model;

use Doctrine\Common\Collections\Collection;
use Ekyna\Component\Resource\Model\ResourceInterface;

/**
 * Interface TaxGroupInterface
 * @package Ekyna\Component\Commerce\Pricing\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface TaxGroupInterface extends ResourceInterface
{
    public function getCode(): ?string;

    public function setCode(string $code): TaxGroupInterface;

    public function getName(): ?string;

    public function setName(string $name): TaxGroupInterface;

    /**
     * Returns whether this is the default tax group.
     */
    public function isDefault(): bool;

    /**
     * Sets whether this is the default tax group.
     */
    public function setDefault(bool $default): TaxGroupInterface;

    /**
     * Returns whether the tax group has taxes.
     */
    public function hasTaxes(): bool;

    /**
     * @return Collection<int, TaxInterface>
     */
    public function getTaxes(): Collection;

    /**
     * Returns whether the tax group has the given tax.
     */
    public function hasTax(TaxInterface $tax): bool;

    public function addTax(TaxInterface $tax): TaxGroupInterface;

    public function removeTax(TaxInterface $tax): TaxGroupInterface;

    /**
     * @param array<TaxInterface> $taxes
     */
    public function setTaxes(array $taxes): TaxGroupInterface;
}
