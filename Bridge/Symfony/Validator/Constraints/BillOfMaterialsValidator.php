<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Bridge\Symfony\Validator\Constraints;

use Ekyna\Component\Commerce\Exception\UnexpectedTypeException;
use Ekyna\Component\Commerce\Exception\ValidationFailedException;
use Ekyna\Component\Commerce\Manufacture\Model\BillOfMaterialsInterface;
use Ekyna\Component\Commerce\Manufacture\Repository\BillOfMaterialsRepositoryInterface;
use Ekyna\Component\Commerce\Subject\Entity\SubjectIdentity as Identity;
use Ekyna\Component\Commerce\Subject\SubjectHelperInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

use function is_null;
use function sprintf;

/**
 * Class BillOfMaterialsValidator
 * @package Ekyna\Component\Commerce\Bridge\Symfony\Validator\Constraints
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class BillOfMaterialsValidator extends ConstraintValidator
{
    public function __construct(
        private readonly BillOfMaterialsRepositoryInterface $repository,
        private readonly SubjectHelperInterface $subjectHelper,
    ) {
    }

    public function validate($value, Constraint $constraint): void
    {
        if (!$value instanceof BillOfMaterialsInterface) {
            throw new UnexpectedTypeException($value, BillOfMaterialsInterface::class);
        }
        if (!$constraint instanceof BillOfMaterials) {
            throw new UnexpectedTypeException($constraint, BillOfMaterials::class);
        }

        // Subject is required
        if (!$value->hasSubjectIdentity()) {
            $this
                ->context
                ->buildViolation('Subject must be set')
                ->atPath('subjectIdentity')
                ->addViolation();

            return;
        }

        $this->validateUnicity($value, $constraint);

        // Children recursivity
        try {
            $this->validateChildrenRecursivity($value, $value->getSubjectIdentity());
        } catch (ValidationFailedException $e) {
            $subject = $this->subjectHelper->resolve($value);
            $this
                ->context
                ->buildViolation(sprintf(
                    'Recursivity: the BOM "%s" has "%s" as component.',
                    $e->getMessage(),
                    $subject
                ))
                ->atPath('components')
                ->addViolation();

            return;
        }

        // Parent recursivity
        try {
            $this->validateParentsRecursivity($value, $value->getSubjectIdentity());
        } catch (ValidationFailedException $e) {
            $subject = $this->subjectHelper->resolve($value);
            $this
                ->context
                ->buildViolation(sprintf(
                    'Recursivity: the BOM "%s" has "%s" as component.',
                    $e->getMessage(),
                    $subject
                ))
                ->atPath('components')
                ->addViolation();

            return;
        }

        // TODO Only one VALIDATED per subject
    }

    private function validateChildrenRecursivity(BillOfMaterialsInterface $parent, Identity $identity): void
    {
        foreach ($parent->getComponents() as $component) {
            if ($component->getSubjectIdentity()->equals($identity)) {
                throw new ValidationFailedException((string)$parent);
            }

            if (null === $child = $this->repository->findOneValidatedBySubject($component)) {
                continue;
            }

            $this->validateChildrenRecursivity($child, $identity);

        }
    }

    private function validateParentsRecursivity(BillOfMaterialsInterface $child, Identity $identity): void
    {
        $parents = $this->repository->findByComponentWithSubject($child->getSubjectIdentity(), true);

        foreach ($parents as $parent) {
            if ($parent->getSubjectIdentity()->equals($identity)) {
                throw new ValidationFailedException((string)$parent);
            }

            $this->validateParentsRecursivity($parent, $identity);
        }
    }

    private function validateUnicity(BillOfMaterialsInterface $bom, BillOfMaterials $constraint): void
    {
        $make = function (string $number) use($constraint): void {
            $this
                ->context
                ->buildViolation($constraint->duplicateMessage, [
                    '{{ number }}' => $number,
                ])
                ->atPath('subjectIdentity')
                ->addViolation();
        };

        $others = $this->repository->findBySubject($bom->getSubjectIdentity());
        if (is_null($bom->getNumber())) {
            if (!empty($others)) {
                $make($others[0]->getNumber());
            }

            return;
        }

        foreach ($others as $other) {
            if ($bom->getNumber() !== $other->getNumber()) {
                $make($other->getNumber());
                return;
            }
        }
    }
}
