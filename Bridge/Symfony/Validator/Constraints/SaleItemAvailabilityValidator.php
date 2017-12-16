<?php

namespace Ekyna\Component\Commerce\Bridge\Symfony\Validator\Constraints;

use Ekyna\Component\Commerce\Common\Model\SaleItemInterface;
use Ekyna\Component\Commerce\Exception\ValidationFailedException;
use Ekyna\Component\Commerce\Order\Model\OrderInterface;
use Ekyna\Component\Commerce\Stock\Helper\AvailabilityHelperInterface;
use Ekyna\Component\Commerce\Stock\Model\StockSubjectInterface;
use Ekyna\Component\Commerce\Subject\SubjectHelperInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * Class SaleItemAvailabilityValidator
 * @package Ekyna\Component\Commerce\Bridge\Symfony\Validator\Constraints
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SaleItemAvailabilityValidator extends ConstraintValidator
{
    /**
     * @var SubjectHelperInterface
     */
    protected $subjectHelper;

    /**
     * @var AvailabilityHelperInterface
     */
    protected $availabilityHelper;


    /**
     * Constructor.
     *
     * @param SubjectHelperInterface      $subjectHelper
     * @param AvailabilityHelperInterface $availabilityHelper
     */
    public function __construct(
        SubjectHelperInterface $subjectHelper,
        AvailabilityHelperInterface $availabilityHelper
    ) {
        $this->subjectHelper = $subjectHelper;
        $this->availabilityHelper = $availabilityHelper;
    }

    /**
     * @inheritdoc
     */
    public function validate($item, Constraint $constraint)
    {
        if (null === $item) {
            return;
        }

        if (!$item instanceof SaleItemInterface) {
            throw new UnexpectedTypeException($item, SaleItemInterface::class);
        }
        if (!$constraint instanceof SaleItemAvailability) {
            throw new UnexpectedTypeException($constraint, SaleItemAvailability::class);
        }

        if (null !== $item->getParent()) {
            // This constraint should not be applied to children
            return;
        }

        $sale = $item->getSale();
        if ($sale instanceof OrderInterface) {
            // This constraint does not applies to orders
            return;
        }

        try {
            $this->validateItem($item);
        } catch (ValidationFailedException $e) {
            $this->context
                ->buildViolation($e->getMessage())
                ->addViolation();
        }
    }

    /**
     * Validates the sale item recursively.
     *
     * @param SaleItemInterface $item
     *
     * @throws ValidationFailedException
     */
    private function validateItem(SaleItemInterface $item)
    {
        if (null === $subject = $this->subjectHelper->resolve($item, false)) {
            return;
        }

        if (!$subject instanceof StockSubjectInterface) {
            return;
        }

        if ($subject->isQuoteOnly()) {
            $message = $this->availabilityHelper->translate('quote_only');
        } else {
            $quantity = $item->getTotalQuantity();
            $max = $this->availabilityHelper->getAvailableQuantity($subject);
            $min = $subject->getMinimumOrderQuantity();

            if (0 == $max && $subject->isEndOfLife()) {
                $message = $this->availabilityHelper->translate('end_of_life');
            } else if ($quantity < $min) {
                $message = $this->availabilityHelper->translate('min_quantity', ['%min%' => $min]);
            } else {
                return;
            }
        }

        if (null !== $item->getParent()) {
            $message = $item->getDesignation() . ' : ' . $message;
        }

        throw new ValidationFailedException($message);
    }
}
