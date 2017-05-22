<?php

namespace Ekyna\Component\Commerce\Customer\Validator\Provider;

use Ekyna\Component\Commerce\Customer\Validator\VatResult;

/**
 * Interface ProviderInterface
 * @package Ekyna\Component\Commerce\Customer\Validator\Provider
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface ProviderInterface
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
