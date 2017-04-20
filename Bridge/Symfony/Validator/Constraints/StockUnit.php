<?php

namespace Ekyna\Component\Commerce\Bridge\Symfony\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * Class StockUnit
 * @package Ekyna\Component\Commerce\Bridge\Symfony\Validator\Constraints
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class StockUnit extends Constraint
{
    public $received_must_be_lower_than_ordered = 'ekyna_commerce.stock_unit.received_must_be_lower_than_ordered';
    public $shipped_must_be_lower_than_received = 'ekyna_commerce.stock_unit.shipped_must_be_lower_than_received';
    public $shipped_must_be_lower_than_sold  = 'ekyna_commerce.stock_unit.shipped_must_be_lower_than_sold';


    /**
     * @inheritDoc
     */
    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
