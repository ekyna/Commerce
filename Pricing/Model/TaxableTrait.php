<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Pricing\Model;

/**
 * Trait TaxableTrait
 * @package Ekyna\Component\Commerce\Pricing\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
trait TaxableTrait
{
    protected ?TaxGroupInterface $taxGroup = null;


    public function getTaxGroup(): ?TaxGroupInterface
    {
        return $this->taxGroup;
    }

    /**
     * @return $this|TaxableInterface
     */
    public function setTaxGroup(?TaxGroupInterface $taxGroup): TaxableInterface
    {
        $this->taxGroup = $taxGroup;

        return $this;
    }
}
