<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Bridge\Symfony\Validator\Constraints;

use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Shipment\Gateway;
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
    private Gateway\GatewayRegistryInterface $gatewayRegistry;

    public function __construct(Gateway\GatewayRegistryInterface $gatewayRegistry)
    {
        $this->gatewayRegistry = $gatewayRegistry;
    }

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
        if (!$constraint instanceof Sale) {
            throw new UnexpectedTypeException($constraint, Sale::class);
        }

        $this->validateIdentity($sale, $constraint);
        $this->validateDeliveryAddress($sale, $constraint);
        $this->validateShipmentMethodRequirements($sale, $constraint);
        $this->validatePaymentTermAndOutstandingLimit($sale, $constraint);

        if (0 < $sale->getDepositTotal() && $sale->getDepositTotal() >= $sale->getGrandTotal()) {
            $this->context
                ->buildViolation($constraint->deposit_greater_than_grand_total)
                ->atPath('depositTotal')
                ->addViolation();
        }
    }

    /**
     * Validates the shipment method requirements.
     */
    protected function validateShipmentMethodRequirements(SaleInterface $sale, Constraint $constraint): void
    {
        if (null === $method = $sale->getShipmentMethod()) {
            return;
        }

        if ($sale->isSameAddress()) {
            $address = $sale->getInvoiceAddress();
            $path = 'invoiceAddress';
        } else {
            $address = $sale->getDeliveryAddress();
            $path = 'deliveryAddress';
        }

        if (null === $address) {
            return;
        }

        $gateway = $this->gatewayRegistry->getGateway($method->getGatewayName());

        if ($gateway->requires(Gateway\GatewayInterface::REQUIREMENT_MOBILE)) {
            if (is_null($address->getMobile())) {
                $this->context
                    ->buildViolation($constraint->shipment_method_require_mobile)
                    ->atPath($path . '.mobile')
                    ->addViolation();
            }
        }
    }

    /**
     * Validates the delivery address.
     */
    protected function validateDeliveryAddress(SaleInterface $sale, Constraint $constraint): void
    {
        /** @var Sale $constraint */
        if (!$sale->isSameAddress() && null === $sale->getDeliveryAddress()) {
            $this->context
                ->buildViolation($constraint->delivery_address_is_required)
                ->atPath('deliveryAddress')
                ->addViolation();

            return;
        }

        if ($sale->isSameAddress() && null !== $sale->getDeliveryAddress()) {
            $this->context
                ->buildViolation($constraint->delivery_address_should_be_null)
                ->atPath('deliveryAddress')
                ->addViolation();
        }
    }

    /**
     * Validates the sale identity.
     */
    protected function validateIdentity(SaleInterface $sale, Constraint $constraint): void
    {
        /** @var Sale $constraint */
        if (null === $sale->getCustomer()) {
            if (null === $sale->getCustomerGroup()) {
                $this->context
                    ->buildViolation($constraint->customer_group_is_required_if_no_customer)
                    ->atPath('customerGroup')
                    ->addViolation();
            }
            if (empty($sale->getEmail())) {
                $this->context
                    ->buildViolation($constraint->email_is_required_if_no_customer)
                    ->atPath('email')
                    ->addViolation();
            }

            IdentityValidator::validateIdentity($this->context, $sale);
        }
    }

    /**
     * Validates the sale payment term and outstanding limit.
     */
    protected function validatePaymentTermAndOutstandingLimit(SaleInterface $sale, Constraint $constraint): void
    {
        if (0 >= $sale->getOutstandingLimit()) {
            return;
        }

        $term = $sale->getPaymentTerm();

        if ($customer = $sale->getCustomer()) {
            if ($customer->hasParent()) {
                $customer = $customer->getParent();
            }

            if (null === $term) {
                $term = $customer->getPaymentTerm();
            }
        }

        if ($customer && !$customer->isOutstandingOverflow()) {
            $this->context
                ->buildViolation($constraint->outstanding_overflow_is_forbidden)
                ->atPath('outstandingLimit')
                ->addViolation();
        }

        if (null === $term) {
            /** @var Sale $constraint */
            $this->context
                ->buildViolation($constraint->outstanding_limit_require_term)
                ->atPath('outstandingLimit')
                ->addViolation();
        }
    }
}
