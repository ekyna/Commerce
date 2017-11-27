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
    public $hierarchy_integrity = 'ekyna_commerce.invoice.hierarchy_integrity';
    public $empty_good_lines    = 'ekyna_commerce.invoice.empty_good_lines';


    /**
     * @inheritDoc
     */
    public function getTargets()
    {
        return static::CLASS_CONSTRAINT;
    }
}
