<?php
/** @noinspection PhpPropertyNamingConventionInspection */

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Bridge\Symfony\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * Class Address
 * @package Ekyna\Component\Commerce\Bridge\Symfony\Validator\Constraints
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class Address extends Constraint
{
    public string $invalid_zip_code = 'ekyna_commerce.address.invalid_zip_code';

    public bool $identity = false;
    public bool $company  = false;
    public bool $phone    = false;
    public bool $mobile   = false;

    /**
     * @inheritDoc
     */
    public function getTargets(): array|string
    {
        return self::CLASS_CONSTRAINT;
    }
}
