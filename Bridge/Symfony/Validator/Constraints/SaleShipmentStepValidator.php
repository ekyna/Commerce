<?php

namespace Ekyna\Component\Commerce\Bridge\Symfony\Validator\Constraints;

use Ekyna\Component\Commerce\Cart\Model\CartInterface;
use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * Class SaleShipmentStepValidator
 * @package Ekyna\Component\Commerce\Bridge\Symfony\Validator\Constraints
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SaleShipmentStepValidator extends ConstraintValidator
{
    /**
     * @inheritDoc
     */
    public function validate($sale, Constraint $constraint)
    {
        if (null === $sale) {
            return;
        }

        if (!$sale instanceof SaleInterface) {
            throw new UnexpectedTypeException($sale, SaleInterface::class);
        }
        if (!$constraint instanceof SaleShipmentStep) {
            throw new UnexpectedTypeException($constraint, SaleShipmentStep::class);
        }

        if ($sale instanceof CartInterface && $sale->isLocked()) {
            $this->context
                ->buildViolation($constraint->cart_is_locked)
                ->addViolation();

            return;
        }

        if (!$this->isIdentityValid($sale)) {
            $this->context
                ->buildViolation($constraint->identity_must_be_set)
                ->addViolation();
        }
        if (null === $sale->getInvoiceAddress()) {
            $this->context
                ->buildViolation($constraint->invoice_address_must_be_set)
                ->addViolation();
        }
        if (!$sale->isSameAddress() && null === $sale->getDeliveryAddress()) {
            $this->context
                ->buildViolation($constraint->delivery_address_must_be_set)
                ->addViolation();
        }
    }

    /**
     * Returns whether the sale identity fields are valid.
     *
     * @param SaleInterface $cart
     *
     * @return bool
     */
    private function isIdentityValid(SaleInterface $cart)
    {
        return !empty($cart->getEmail())
            && !empty($cart->getGender())
            && !empty($cart->getFirstName())
            && !empty($cart->getLastName());
    }
}
