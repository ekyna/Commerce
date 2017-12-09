<?php

namespace Ekyna\Component\Commerce\Bridge\Symfony\Validator\Constraints;

use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Quote\Model\QuoteInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * Class SalePaymentStepValidator
 * @package Ekyna\Component\Commerce\Bridge\Symfony\Validator\Constraints
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SalePaymentStepValidator extends ConstraintValidator
{
    /**
     * @inheritdoc
     */
    public function validate($sale, Constraint $constraint)
    {
        if (null === $sale) {
            return;
        }

        if (!$sale instanceof SaleInterface) {
            throw new UnexpectedTypeException($sale, SaleInterface::class);
        }
        if (!$constraint instanceof SalePaymentStep) {
            throw new UnexpectedTypeException($constraint, SalePaymentStep::class);
        }

        if (null === $sale->getPreferredShipmentMethod()) {
            $this->context
                ->buildViolation($constraint->shipment_method_must_be_set)
                ->addViolation();
        }

        if ($sale instanceof QuoteInterface && !$sale->hasVoucher()) {
            $this->context
                ->buildViolation($constraint->voucher_must_be_set)
                ->addViolation();
        }
    }
}
