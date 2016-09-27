<?php

namespace Ekyna\Component\Commerce\Bridge\Symfony\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * Class GenderValidator
 * @package Ekyna\Component\Commerce\Bridge\Symfony\Validator\Constraints
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
class GenderValidator extends ConstraintValidator
{
    /**
     * @var string
     */
    private $genderClass;


    /**
     * Constructor.
     *
     * @param $genderClass
     */
    public function __construct($genderClass)
    {
        $this->genderClass = $genderClass;
    }

    /**
     * {@inheritdoc}
     */
    public function validate($gender, Constraint $constraint)
    {
        if (null === $gender) {
            return;
        }

        if (!$constraint instanceof Gender) {
            throw new UnexpectedTypeException($constraint, __NAMESPACE__.'\Gender');
        }

        if (!call_user_func($this->genderClass.'::isValid', $gender)) {
            $this->context
                ->buildViolation($constraint->invalidGender)
                ->addViolation();
        }
    }
}
