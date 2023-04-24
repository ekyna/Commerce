<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Bridge\Symfony\Validator\Constraints;

use Ekyna\Component\Commerce\Supplier\Model\SupplierOrderItemInterface;
use Ekyna\Component\Commerce\Supplier\Util\SupplierUtil;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * Class SupplierOrderItemValidator
 * @package Ekyna\Component\Commerce\Bridge\Symfony\Validator\Constraints
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SupplierOrderItemValidator extends ConstraintValidator
{
    /**
     * @inheritDoc
     */
    public function validate($value, Constraint $constraint): void
    {
        if (!$value instanceof SupplierOrderItemInterface) {
            throw new UnexpectedTypeException($value, SupplierOrderItemInterface::class);
        }
        if (!$constraint instanceof SupplierOrderItem) {
            throw new UnexpectedTypeException($constraint, SupplierOrderItem::class);
        }

        if (null === $value->getId()) {
            return;
        }

        if ($value->getQuantity() < $min = SupplierUtil::calculateReceivedQuantity($value)) {
            $this
                ->context
                ->buildViolation($constraint->quantity_must_be_greater_than_or_equal_received, [
                    '%min%' => $min
                ])
                ->atPath('quantity')
                ->addViolation();
        }
    }
}
