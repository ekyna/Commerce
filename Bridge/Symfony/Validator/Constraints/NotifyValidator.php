<?php

namespace Ekyna\Component\Commerce\Bridge\Symfony\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Ekyna\Component\Commerce\Common\Model\Notify as Model;
use Symfony\Component\Validator\Exception\InvalidArgumentException;

/**
 * Class NotificationValidator
 * @package Ekyna\Component\Commerce\Bridge\Symfony\Validator\Constraints
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class NotifyValidator extends ConstraintValidator
{
    /**
     * @inheritDoc
     */
    public function validate($notify, Constraint $constraint)
    {
        if (!$notify instanceof Model) {
            throw new InvalidArgumentException("Expected instance of " . Model::class);
        }
        if (!$constraint instanceof Notify) {
            throw new InvalidArgumentException("Expected instance of " . Notify::class);
        }

        if (0 === $notify->getRecipients()->count() && 0 === $notify->getExtraRecipients()->count()) {
            $this
                ->context
                ->buildViolation($constraint->pick_at_least_one_recipient)
                ->addViolation();
        }
        
        if ($notify->isEmpty()) {
            $this
                ->context
                ->buildViolation($constraint->is_empty)
                ->atPath('customMessage')
                ->addViolation();
        }
    }
}
