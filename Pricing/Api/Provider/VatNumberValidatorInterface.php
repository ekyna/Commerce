<?php

namespace Ekyna\Component\Commerce\Pricing\Api\Provider;

/**
 * Class VatNumberValidatorInterface
 * @package Ekyna\Component\Commerce\Tax\Provider
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface VatNumberValidatorInterface
{
    /**
     * Validates the given VAT number.
     *
     * @param string $vatNumber
     *
     * @return \Ekyna\Component\Commerce\Pricing\Api\VatNumberResult|null
     *         The result or null if the given number's format is not supported
     */
    public function validateVatNumber($vatNumber);
}
