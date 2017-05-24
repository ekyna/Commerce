<?php

namespace Ekyna\Component\Commerce\Pricing\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Ekyna\Component\Resource\Model\ResourceInterface;

/**
 * Interface TaxGroupInterface
 * @package Ekyna\Component\Commerce\Pricing\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface TaxGroupInterface extends ResourceInterface
{
    /**
     * Returns the name.
     *
     * @return string
     */
    public function getName();

    /**
     * Sets the name.
     *
     * @param string $name
     * @return $this|TaxGroupInterface
     */
    public function setName($name);

    /**
     * Returns whether this is the default tax group.
     *
     * @return boolean
     */
    public function isDefault();

    /**
     * Sets whether this is the default tax group.
     *
     * @param boolean $default
     *
     * @return $this|TaxGroupInterface
     */
    public function setDefault($default);

    /**
     * Returns whether the tax group has taxes.
     *
     * @return bool
     */
    public function hasTaxes();

    /**
     * Returns the taxes.
     *
     * @return ArrayCollection|TaxInterface[]
     */
    public function getTaxes();

    /**
     * Returns whether the tax group has the given tax.
     *
     * @param TaxInterface $tax
     *
     * @return bool
     */
    public function hasTax(TaxInterface $tax);

    /**
     * Adds the tax.
     *
     * @param TaxInterface $tax
     *
     * @return $this|TaxGroupInterface
     */
    public function addTax(TaxInterface $tax);

    /**
     * Removes the tax.
     *
     * @param TaxInterface $tax
     *
     * @return $this|TaxGroupInterface
     */
    public function removeTax(TaxInterface $tax);

    /**
     * Sets the taxes.
     *
     * @param TaxInterface[] $taxes
     *
     * @return $this|TaxGroupInterface
     */
    public function setTaxes(array $taxes);
}
