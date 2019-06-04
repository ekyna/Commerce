<?php

namespace Ekyna\Component\Commerce\Bridge\Symfony\Validator\Constraints;

use Ekyna\Component\Commerce\Subject\SubjectHelperInterface;
use Ekyna\Component\Commerce\Supplier\Model\SupplierProductInterface;
use Ekyna\Component\Commerce\Supplier\Repository\SupplierProductRepositoryInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\InvalidArgumentException;

/**
 * Class SupplierProductValidator
 * @package Ekyna\Component\Commerce\Bridge\Symfony\Validator\Constraints
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SupplierProductValidator extends ConstraintValidator
{
    /**
     * @var SupplierProductRepositoryInterface
     */
    private $repository;

    /**
     * @var SubjectHelperInterface
     */
    private $subjectHelper;


    /**
     * Constructor.
     *
     * @param SupplierProductRepositoryInterface $repository
     * @param SubjectHelperInterface             $subjectHelper
     */
    public function __construct(SupplierProductRepositoryInterface $repository, SubjectHelperInterface $subjectHelper)
    {
        $this->repository = $repository;
        $this->subjectHelper = $subjectHelper;
    }

    /**
     * @inheritDoc
     */
    public function validate($value, Constraint $constraint)
    {
        if (null === $value) {
            return;
        }

        if (!$value instanceof SupplierProductInterface) {
            throw new InvalidArgumentException("Expected instance of " . SupplierProductInterface::class);
        }
        if (!$constraint instanceof SupplierProduct) {
            throw new InvalidArgumentException("Expected instance of " . SupplierProduct::class);
        }

        if (!$value->hasSubjectIdentity()) {
            return;
        }

        /** @var \Ekyna\Component\Commerce\Subject\Model\SubjectInterface $subject */
        $subject = $this->subjectHelper->resolve($value);

        $duplicate = $this->repository->findOneBySubjectAndSupplier(
            $subject,
            $value->getSupplier(),
            null !== $value->getId() ? $value : null
        );

        if (null !== $duplicate) {
            $this->context
                ->buildViolation($constraint->duplicate_by_subject)
                ->atPath('subjectIdentity')
                ->addViolation();
        }
    }
}
