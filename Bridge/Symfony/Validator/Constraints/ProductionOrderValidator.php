<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Bridge\Symfony\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Class ProductionOrderValidator
 * @package Ekyna\Component\Commerce\Bridge\Symfony\Validator\Constraints
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ProductionOrderValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint): void
    {
        // TODO BOM must be in a validated state

        // TODO State can't change to not stockable if it has at least one production
    }
}
