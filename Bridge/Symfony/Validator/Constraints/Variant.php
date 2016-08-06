<?php

namespace Ekyna\Component\Commerce\Bridge\Symfony\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * Class Variant
 * @package Ekyna\Component\Commerce\Bridge\Symfony\Validator\Constraints
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class Variant extends Constraint
{
    public $slotAttributeIsMandatory = 'ekyna_commerce.product.slot_attribute_is_mandatory';
    public $slotHasTooManyAttributes = 'ekyna_commerce.product.slot_has_too_many_attributes';
    public $unexpectedAttribute      = 'ekyna_commerce.product.unexpected_attribute';
    public $variantIsNotUnique       = 'ekyna_commerce.product.variant_is_not_unique';

    /**
     * {@inheritdoc}
     */
    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
