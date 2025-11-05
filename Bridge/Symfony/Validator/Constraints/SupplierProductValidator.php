<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Bridge\Symfony\Validator\Constraints;

use Ekyna\Component\Commerce\Exception\UnexpectedTypeException;
use Ekyna\Component\Commerce\Subject\Model\SubjectInterface;
use Ekyna\Component\Commerce\Subject\SubjectHelperInterface;
use Ekyna\Component\Commerce\Supplier\Model\SupplierProductInterface;
use Ekyna\Component\Commerce\Supplier\Repository\SupplierProductRepositoryInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Class SupplierProductValidator
 * @package Ekyna\Component\Commerce\Bridge\Symfony\Validator\Constraints
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SupplierProductValidator extends ConstraintValidator
{
    public function __construct(
        private readonly SupplierProductRepositoryInterface $repository,
        private readonly SubjectHelperInterface             $subjectHelper
    ) {
    }

    /**
     * @inheritDoc
     */
    public function validate($value, Constraint $constraint): void
    {
        if (null === $value) {
            return;
        }

        if (!$value instanceof SupplierProductInterface) {
            throw new UnexpectedTypeException($value, SupplierProductInterface::class);
        }
        if (!$constraint instanceof SupplierProduct) {
            throw new UnexpectedTypeException($constraint, SupplierProduct::class);
        }

        $this->validateUnique($value, $constraint);
    }

    private function validateUnique(SupplierProductInterface $product, SupplierProduct $constraint): void
    {
        if (!$product->hasSubjectIdentity()) {
            return;
        }

        /** @var SubjectInterface $subject */
        $subject = $this->subjectHelper->resolve($product);

        $duplicate = $this->repository->findOneBySubjectAndSupplier(
            $subject,
            $product->getSupplier(),
            null !== $product->getId() ? $product : null
        );

        if (null === $duplicate) {
            return;
        }

        $this->context
            ->buildViolation($constraint->duplicate_by_subject)
            ->atPath('subjectIdentity')
            ->addViolation();
    }
}
