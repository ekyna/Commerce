<?php

namespace Ekyna\Component\Commerce\Bridge\Symfony\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * Class Gender
 * @package Ekyna\Component\Commerce\Bridge\Symfony\Validator\Constraints
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
class Gender extends Constraint
{
    public $invalidGender = 'ekyna_commerce.gender.invalid';
}
