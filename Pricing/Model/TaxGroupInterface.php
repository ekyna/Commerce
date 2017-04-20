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
    /**
     * Returns the code.
     */
    public function getCode(): ?string;

    /**
     * Sets the code.
     *
     * @return $this|TaxGroupInterface
     */
    public function setCode(string $code): TaxGroupInterface;

    /**
     * Returns the name.
     */
    public function getName(): ?string;

    /**
     * Sets the name.
     *
     * @return $this|TaxGroupInterface
     */
    public function setName(string $name): TaxGroupInterface;

    /**
     * Returns whether this is the default tax group.
     */
    public function isDefault(): bool;

    /**
     * Sets whether this is the default tax group.
     *
     * @return $this|TaxGroupInterface
     */
    public function setDefault(bool $default): TaxGroupInterface;

    /**
     * Returns whether the tax group has taxes.
     */
    public function hasTaxes(): bool;

    /**
     * Returns the taxes.
     *
     * @return Collection|TaxInterface[]
     */
    public function getTaxes(): Collection;

    /**
     * Returns whether the tax group has the given tax.
     */
    public function hasTax(TaxInterface $tax): bool;

    /**
     * Adds the tax.
     *
     * @return $this|TaxGroupInterface
     */
    public function addTax(TaxInterface $tax): TaxGroupInterface;

    /**
     * Removes the tax.
     *
     * @return $this|TaxGroupInterface
     */
    public function removeTax(TaxInterface $tax): TaxGroupInterface;

    /**
     * Sets the taxes.
     *
     * @param TaxInterface[] $taxes
     *
     * @return $this|TaxGroupInterface
     */
    public function setTaxes(array $taxes): TaxGroupInterface;
}
