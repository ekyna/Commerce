<?php

namespace Ekyna\Component\Commerce\Pricing\Api;

/**
 * Interface PricingApiInterface
 * @package Ekyna\Component\Commerce\Tax
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface PricingApiInterface
{
    /**
     * Validates the given VAT number.
     *
     * @param string $vatNumber
     *
     * @return VatNumberResult|null The result or null if the given number's format is not supported
     */
    public function validateVatNumber($vatNumber);
}
