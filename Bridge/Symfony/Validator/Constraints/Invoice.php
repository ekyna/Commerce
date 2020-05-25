<?php

namespace Ekyna\Component\Commerce\Bridge\Symfony\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * Class Invoice
 * @package Ekyna\Component\Commerce\Bridge\Symfony\Validator\Constraints
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class Invoice extends Constraint
{
    public $hierarchy_integrity       = 'ekyna_commerce.invoice.hierarchy_integrity';
    public $at_least_one_line_or_item = 'ekyna_commerce.invoice.at_least_one_line_or_item';


    /**
     * @inheritDoc
     */
    public function getTargets()
    {
        return static::CLASS_CONSTRAINT;
    }
}
