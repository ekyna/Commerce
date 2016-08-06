<?php

namespace Ekyna\Component\Commerce\Bridge\Symfony\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * Class BundleChoice
 * @package Ekyna\Component\Commerce\Bridge\Symfony\Validator\Constraints
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class BundleChoice extends Constraint
{
    public $invalidQuantityRange = 'ekyna_commerce.bundle_choice.invalid_quantity_range';
    public $rulesShouldBeEmpty = 'ekyna_commerce.bundle_choice.rules_should_be_empty';

    /**
     * {@inheritdoc}
     */
    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
