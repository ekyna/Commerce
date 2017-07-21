<?php

namespace Ekyna\Component\Commerce\Bridge\Symfony\Validator\Constraints;

use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * Class SaleValidator
 * @package Ekyna\Component\Commerce\Bridge\Symfony\Validator\Constraints
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SaleValidator extends ConstraintValidator
{
    /**
     * {@inheritdoc}
     */
    public function validate($sale, Constraint $constraint)
    {
        if (null === $sale) {
            return;
        }

        if (!$sale instanceof SaleInterface) {
            throw new UnexpectedTypeException($sale, SaleInterface::class);
        }
        if (!$constraint instanceof Sale) {
            throw new UnexpectedTypeException($constraint, Sale::class);
        }

        $this->validateIdentity($sale, $constraint);
        $this->validateDeliveryAddress($sale, $constraint);
    }

    /**
     * Validates the delivery address.
     *
     * @param SaleInterface $sale
     * @param Constraint    $constraint
     */
    protected function validateDeliveryAddress(SaleInterface $sale, Constraint $constraint)
    {
        /** @var Sale $constraint */
        if (!$sale->isSameAddress() && null === $sale->getDeliveryAddress()) {
            $this->context
                ->buildViolation($constraint->delivery_address_is_required)
                ->atPath('deliveryAddress')
                ->addViolation();

        } elseif ($sale->isSameAddress() && null !== $sale->getDeliveryAddress()) {
            $this->context
                ->buildViolation($constraint->delivery_address_should_be_null)
                ->atPath('deliveryAddress')
                ->addViolation();
        }
    }

    /**
     * Validates the sale identity.
     *
     * @param SaleInterface $sale
     * @param Constraint    $constraint
     */
    protected function validateIdentity(SaleInterface $sale, Constraint $constraint)
    {
        /** @var Sale $constraint */
        if (null === $sale->getCustomer()) {
            if (null === $sale->getCustomerGroup()) {
                $this->context
                    ->buildViolation($constraint->customer_group_is_required_if_no_customer)
                    ->atPath('customerGroup')
                    ->addViolation();
            }
            if (0 == strlen($sale->getEmail())) {
                $this->context
                    ->buildViolation($constraint->email_is_required_if_no_customer)
                    ->atPath('email')
                    ->addViolation();
            }

            IdentityValidator::validateIdentity($this->context, $sale);
        }
    }
}
