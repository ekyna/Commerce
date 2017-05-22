<?php

namespace Ekyna\Component\Commerce\Customer\Validator;

/**
 * Interface VatNumberValidatorInterface
 * @package Ekyna\Component\Commerce\Customer\Validator
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface VatNumberValidatorInterface
{
    /**
     * Validates the given VAT number.
     *
     * @param string $vatNumber
     *
     * @return VatResult|null The result or null if the given number's format is not supported
     */
    public function validate($vatNumber);
}
