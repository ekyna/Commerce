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
    public function getTaxGroup()
    {
        return $this->taxGroup;
    }

    /**
     * Sets the tax group.
     *
     * @param TaxGroupInterface $taxGroup
     *
     * @return $this|TaxGroupInterface
     */
    public function setTaxGroup(TaxGroupInterface $taxGroup = null)
    {
        $this->taxGroup = $taxGroup;

        return $this;
    }
}
