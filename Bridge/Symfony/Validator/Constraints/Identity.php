<?php

namespace Ekyna\Component\Commerce\Bridge\Symfony\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * Class Identity
 * @package Ekyna\Component\Commerce\Bridge\Symfony\Validator\Constraints
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
class Identity extends Constraint
{
    public $mandatory               = 'ekyna_commerce.identity.mandatory';
    public $gender_is_mandatory     = 'ekyna_commerce.identity.gender_is_mandatory';
    public $first_name_is_mandatory = 'ekyna_commerce.identity.first_name_is_mandatory';
    public $last_name_is_mandatory  = 'ekyna_commerce.identity.last_name_is_mandatory';

    public $required = true;


    /**
     * @inheritDoc
     */
    public function getDefaultOption()
    {
        return 'required';
    }

    /**
     * {@inheritdoc}
     */
    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
