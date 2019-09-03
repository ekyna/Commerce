<?php

namespace Ekyna\Component\Commerce\Pricing\Model;

/**
 * Trait TaxableTrait
 * @package Ekyna\Component\Commerce\Pricing\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
trait TaxableTrait
{
    /**
     * @var TaxGroupInterface
     */
    protected $taxGroup;


    /**
     * Returns the tax group.
     *
     * @return TaxGroupInterface
     */
    public function getTaxGroup(): ?TaxGroupInterface
    {
        return $this->taxGroup;
    }

    /**
     * Sets the tax group.
     *
     * @param TaxGroupInterface $taxGroup
     *
     * @return $this|TaxableInterface
     */
    public function setTaxGroup(TaxGroupInterface $taxGroup = null): TaxableInterface
    {
        $this->taxGroup = $taxGroup;

        return $this;
    }
}
