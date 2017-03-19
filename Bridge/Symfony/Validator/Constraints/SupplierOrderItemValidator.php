<?php

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
    public function validate($item, Constraint $constraint)
    {
        if (null === $item) {
            return;
        }

        if (!$item instanceof SupplierOrderItemInterface) {
            throw new UnexpectedTypeException($item, SupplierOrderItemInterface::class);
        }
        if (!$constraint instanceof SupplierOrderItem) {
            throw new UnexpectedTypeException($constraint, SupplierOrderItem::class);
        }

        if ($item->getId() && ($item->getQuantity() < $min = SupplierUtil::calculateDeliveredQuantity($item))) {
            $this
                ->context
                ->buildViolation($constraint->quantity_must_be_greater_or_equal_than_delivered, [
                    '%min%' => $min
                ])
                ->atPath('quantity')
                ->addViolation();
        }
    }
}
