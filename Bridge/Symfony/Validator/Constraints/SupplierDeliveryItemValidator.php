<?php

namespace Ekyna\Component\Commerce\Bridge\Symfony\Validator\Constraints;

use Ekyna\Component\Commerce\Supplier\Model\SupplierDeliveryItemInterface;
use Ekyna\Component\Commerce\Supplier\Util\SupplierUtil;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * Class SupplierDeliveryItemValidator
 * @package Ekyna\Component\Commerce\Bridge\Symfony\Validator\Constraints
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SupplierDeliveryItemValidator extends ConstraintValidator
{
    /**
     * @inheritDoc
     */
    public function validate($item, Constraint $constraint)
    {
        if (null === $item) {
            return;
        }

        if (!$item instanceof SupplierDeliveryItemInterface) {
            throw new UnexpectedTypeException($item, SupplierDeliveryItemInterface::class);
        }
        if (!$constraint instanceof SupplierDeliveryItem) {
            throw new UnexpectedTypeException($constraint, SupplierDeliveryItem::class);
        }

        if ($item->getQuantity() > $max = SupplierUtil::calculateDeliveryRemainingQuantity($item)) {
            $this
                ->context
                ->buildViolation($constraint->quantity_must_be_lower_or_equal_than_ordered, [
                    '%max%' => $max
                ])
                ->atPath('quantity')
                ->addViolation();
        }
    }
}
