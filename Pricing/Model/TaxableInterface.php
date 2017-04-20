<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Pricing\Model;

/**
 * Interface TaxableInterface
 * @package Ekyna\Component\Commerce\Pricing\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface TaxableInterface
{
    public function getTaxGroup(): ?TaxGroupInterface;

    public function setTaxGroup(?TaxGroupInterface $taxGroup): TaxableInterface;
}
