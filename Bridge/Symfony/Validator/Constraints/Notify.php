<?php

namespace Ekyna\Component\Commerce\Bridge\Symfony\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * Class Notification
 * @package Ekyna\Component\Commerce\Bridge\Symfony\Validator\Constraints
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class Notify extends Constraint
{
    public $pick_at_least_one_recipient = 'ekyna_commerce.notification.pick_at_least_one_recipient';


    /**
     * @inheritDoc
     */
    public function getTargets()
    {
        return static::CLASS_CONSTRAINT;
    }
}
