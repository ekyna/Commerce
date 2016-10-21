<?php

namespace Ekyna\Component\Commerce\Pricing\Model;

/**
 * Interface TaxableInterface
 * @package Ekyna\Component\Commerce\Pricing\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface TaxableInterface
{
    /**
     * Returns the tax group.
     *
     * @return TaxGroupInterface
     */
    public function getTaxGroup();

    /**
     * Sets the tax group.
     *
     * @param TaxGroupInterface $taxGroup
     *
     * @return $this|TaxableInterface
     */
    public function setTaxGroup(TaxGroupInterface $taxGroup = null);
}
