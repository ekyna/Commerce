<?php

namespace Ekyna\Component\Commerce\Bridge\Symfony\Validator\Constraints;

use Ekyna\Component\Commerce\Subject\Entity\SubjectIdentity as Entity;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * Class SubjectIdentityValidator
 * @package Ekyna\Component\Commerce\Bridge\Symfony\Validator\Constraints
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SubjectIdentityValidator extends ConstraintValidator
{
    /**
     * @inheritDoc
     */
    public function validate($identity, Constraint $constraint)
    {
        if (null === $identity) {
            return;
        }

        if (!$identity instanceof Entity) {
            throw new UnexpectedTypeException($identity, Entity::class);
        }
        if (!$constraint instanceof SubjectIdentity) {
            throw new UnexpectedTypeException($constraint, SubjectIdentity::class);
        }

        if (empty($identity->getProvider()) xor empty($identity->getIdentifier())) {
            $this->context
                ->buildViolation($constraint->identity_subject_must_be_selected)
                ->atPath('provider')
                ->addViolation();
        }
    }
}
