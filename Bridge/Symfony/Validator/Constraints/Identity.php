<?php /** @noinspection PhpPropertyNamingConventionInspection */

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Bridge\Symfony\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * Class Identity
 * @package Ekyna\Component\Commerce\Bridge\Symfony\Validator\Constraints
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
class Identity extends Constraint
{
    public string $mandatory               = 'ekyna_commerce.identity.mandatory';
    public string $gender_is_mandatory     = 'ekyna_commerce.identity.gender_is_mandatory';
    public string $first_name_is_mandatory = 'ekyna_commerce.identity.first_name_is_mandatory';
    public string $last_name_is_mandatory  = 'ekyna_commerce.identity.last_name_is_mandatory';

    public bool $required = true;


    /**
     * @inheritDoc
     */
    public function getDefaultOption(): ?string
    {
        return 'required';
    }

    /**
     * @inheritDoc
     */
    public function getTargets(): array|string
    {
        return self::CLASS_CONSTRAINT;
    }
}
