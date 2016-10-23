<?php

namespace Ekyna\Component\Commerce\Bridge\Symfony\Validator\Constraints;

use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Class OrderValidator
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
        /**
         * @var SaleInterface $sale
         * @var Sale          $constraint
         */

        $this->validateIdentity($sale, $constraint);

        if ($sale->requiresShipment()) {
            if (null === $sale->getPreferredShipmentMethod()) {
                // TODO
            }
            if (!$sale->isSameAddress() && null === $sale->getDeliveryAddress()) {
                $this->context
                    ->buildViolation($constraint->delivery_address_is_required)
                    ->atPath('deliveryAddress');
            }
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
                    ->atPath('customerGroup');
            }
            if (0 == strlen($sale->getEmail())) {
                $this->context
                    ->buildViolation($constraint->email_is_required_if_no_customer)
                    ->atPath('email');
            }
            if (0 == strlen($sale->getGender())) {
                $this->context
                    ->buildViolation($constraint->identity_is_required_if_no_customer)
                    ->atPath('gender');

                return;
            }
            if (0 == strlen($sale->getFirstName())) {
                $this->context
                    ->buildViolation($constraint->identity_is_required_if_no_customer)
                    ->atPath('firstName');

                return;
            }
            if (0 == strlen($sale->getLastName())) {
                $this->context
                    ->buildViolation($constraint->identity_is_required_if_no_customer)
                    ->atPath('lastName');

                return;
            }
        }
    }
}
