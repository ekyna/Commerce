<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Bridge\Symfony\Validator\Constraints;

use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Common\Repository\CountryRepositoryInterface;
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
    public function __construct(
        private readonly CountryRepositoryInterface $countryRepository,
        private readonly Gateway\GatewayRegistryInterface $gatewayRegistry,
    ) {

    }

    /**
     * @inheritDoc
     */
    public function validate($value, Constraint $constraint): void
    {
        if (null === $value) {
            return;
        }

        if (!$value instanceof SaleInterface) {
            throw new UnexpectedTypeException($value, SaleInterface::class);
        }
        if (!$constraint instanceof Sale) {
            throw new UnexpectedTypeException($constraint, Sale::class);
        }

        $this->validateIdentity($value, $constraint);
        $this->validateDeliveryAddress($value, $constraint);
        $this->validateShipmentMethodRequirements($value, $constraint);
        $this->validateIncoterm($value, $constraint);
        $this->validatePaymentMethod($value, $constraint);
        $this->validatePaymentTermAndOutstandingLimit($value, $constraint);

        if (0 < $value->getDepositTotal() && $value->getDepositTotal() >= $value->getGrandTotal()) {
            $this->context
                ->buildViolation($constraint->deposit_greater_than_grand_total)
                ->atPath('depositTotal')
                ->addViolation();
        }
    }

    /**
     * Validates the shipment method requirements.
     */
    protected function validateShipmentMethodRequirements(SaleInterface $sale, Sale $constraint): void
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

    protected function validateIncoterm(SaleInterface $sale, Sale $constraint): void
    {
        $address = $sale->isSameAddress() ? $sale->getInvoiceAddress() : $sale->getDeliveryAddress();

        if (null === $address) {
            return;
        }

        if ($address->getCountry() === $this->countryRepository->findDefault()) {
            return;
        }

        if (!empty($sale->getIncoterm())) {
            return;
        }

        $this->context
            ->buildViolation($constraint->incoterm_is_required)
            ->atPath('incoterm')
            ->addViolation();
    }

    /**
     * Validates the delivery address.
     */
    protected function validateDeliveryAddress(SaleInterface $sale, Sale $constraint): void
    {
        if ($sale->isSameAddress()) {
            if (null !== $sale->getDeliveryAddress()) {
                $this->context
                    ->buildViolation($constraint->delivery_address_should_be_null)
                    ->atPath('deliveryAddress')
                    ->addViolation();
            }

            return;
        }

        if (null !== $sale->getDeliveryAddress()) {
            return;
        }

        $this->context
            ->buildViolation($constraint->delivery_address_is_required)
            ->atPath('deliveryAddress')
            ->addViolation();
    }

    /**
     * Validates the sale identity.
     */
    protected function validateIdentity(SaleInterface $sale, Sale $constraint): void
    {
        if (null !== $sale->getCustomer()) {
            return;
        }

        IdentityValidator::validateIdentity($this->context, $sale);

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
    }

    /**
     * Validates the sale payment method.
     */
    protected function validatePaymentMethod(SaleInterface $sale, Sale $constraint): void
    {
        if (null === $method = $sale->getPaymentMethod()) {
            return;
        }

        if (!$method->isFactor()) {
            return;
        }

        if (null !== $sale->getPaymentTerm()) {
            return;
        }

        $this->context
            ->buildViolation($constraint->term_required_for_factor_method)
            ->atPath('paymentMethod')
            ->addViolation();
    }

    /**
     * Validates the sale payment term and outstanding limit.
     */
    protected function validatePaymentTermAndOutstandingLimit(SaleInterface $sale, Sale $constraint): void
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
            $this->context
                ->buildViolation($constraint->outstanding_limit_require_term)
                ->atPath('outstandingLimit')
                ->addViolation();
        }
    }
}
