<?php

namespace Ekyna\Component\Commerce\Bridge\Symfony\Validator\Constraints;

use Ekyna\Component\Commerce\Subject\Entity\SubjectIdentity as Entity;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Class SubjectIdentityValidator
 * @package Ekyna\Component\Commerce\Bridge\Symfony\Validator\Constraints
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SubjectIdentityValidator extends ConstraintValidator
{
    /**
     * {@inheritdoc}
     */
    public function validate($identity, Constraint $constraint)
    {
        /**
         * @var Entity $identity
         * @var SubjectIdentity $constraint
         */

        /*if (!in_array('subject_choice', $constraint->groups)) {
            return;
        }*/

        if (!empty($identity->getProvider()) && empty($identity->getIdentifier())) {
            $this->context
                ->buildViolation($constraint->identity_subject_must_be_selected)
                ->atPath('provider')
                ->addViolation();
        }

        $stop = true;
        // TODO test subject resolution
    }
}
