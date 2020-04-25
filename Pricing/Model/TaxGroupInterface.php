<?php

namespace Ekyna\Component\Commerce\Pricing\Model;

use Doctrine\Common\Collections\ArrayCollection;
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
     *
     * @return string
     */
    public function getCode(): ?string;

    /**
     * Sets the code.
     *
     * @param string $code
     *
     * @return $this|TaxGroupInterface
     */
    public function setCode(string $code): TaxGroupInterface;

    /**
     * Returns the name.
     *
     * @return string
     */
    public function getName(): ?string;

    /**
     * Sets the name.
     *
     * @param string $name
     *
     * @return $this|TaxGroupInterface
     */
    public function setName(string $name): TaxGroupInterface;

    /**
     * Returns whether this is the default tax group.
     *
     * @return boolean
     */
    public function isDefault(): bool;

    /**
     * Sets whether this is the default tax group.
     *
     * @param boolean $default
     *
     * @return $this|TaxGroupInterface
     */
    public function setDefault(bool $default): TaxGroupInterface;

    /**
     * Returns whether the tax group has taxes.
     *
     * @return bool
     */
    public function hasTaxes(): bool;

    /**
     * Returns the taxes.
     *
     * @return ArrayCollection|TaxInterface[]
     */
    public function getTaxes(): Collection;

    /**
     * Returns whether the tax group has the given tax.
     *
     * @param TaxInterface $tax
     *
     * @return bool
     */
    public function hasTax(TaxInterface $tax): bool;

    /**
     * Adds the tax.
     *
     * @param TaxInterface $tax
     *
     * @return $this|TaxGroupInterface
     */
    public function addTax(TaxInterface $tax): TaxGroupInterface;

    /**
     * Removes the tax.
     *
     * @param TaxInterface $tax
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
