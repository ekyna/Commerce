<?php

namespace Ekyna\Component\Commerce\Bridge\Symfony\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * Class SaleItemAvailability
 * @package Ekyna\Component\Commerce\Bridge\Symfony\Validator\Constraints
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SaleItemAvailability extends Constraint
{
    /**
     * @inheritDoc
     */
    public function getTargets()
    {
        return static::CLASS_CONSTRAINT;
    }
}
