<?php

namespace Ekyna\Component\Commerce\Bridge\Symfony\Validator\Constraints;

use Ekyna\Component\Commerce\Shipment\Gateway;
use Ekyna\Component\Commerce\Order\Model\OrderInterface;
use Ekyna\Component\Commerce\Order\Model\OrderStates;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentInterface;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentStates;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * Class ShipmentValidator
 * @package Ekyna\Component\Commerce\Bridge\Symfony\Validator\Constraints
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ShipmentValidator extends ConstraintValidator
{
    /**
     * @var Gateway\RegistryInterface
     */
    private $registry;


    /**
     * Constructor.
     *
     * @param Gateway\RegistryInterface $gatewayRegistry
     */
    public function __construct(Gateway\RegistryInterface $gatewayRegistry)
    {
        $this->registry = $gatewayRegistry;
    }

    /**
     * @inheritDoc
     */
    public function validate($shipment, Constraint $constraint)
    {
        if (null === $shipment) {
            return;
        }

        if (!$shipment instanceof ShipmentInterface) {
            throw new UnexpectedTypeException($shipment, ShipmentInterface::class);
        }
        if (!$constraint instanceof Shipment) {
            throw new UnexpectedTypeException($constraint, Shipment::class);
        }

        /**
         * Shipment can't have a stockable state if order is not in a stockable state
         */
        if (ShipmentStates::isStockableState($shipment->getState())) {
            $sale = $shipment->getSale();

            // Only orders are supported.
            if (!$sale instanceof OrderInterface) {
                throw new UnexpectedTypeException($sale, OrderInterface::class);
            }
            if (!OrderStates::isStockableState($sale->getState())) {
                $this
                    ->context
                    ->buildViolation($constraint->shipped_state_denied)
                    ->setInvalidValue($shipment->getState())
                    ->atPath('state')
                    ->addViolation();
            }
        }

        $method = $shipment->getMethod();

        $gateway = $this->registry->getGateway($method->getGatewayName());

        if ($shipment->isReturn() && !$gateway->supports(Gateway\GatewayInterface::CAPABILITY_RETURN)) {
            $this
                ->context
                ->buildViolation($constraint->method_does_not_support_return)
                ->atPath('method')
                ->addViolation();
        } elseif (!$shipment->isReturn() && !$gateway->supports(Gateway\GatewayInterface::CAPABILITY_SHIPMENT)) {
            $this
                ->context
                ->buildViolation($constraint->method_does_not_support_shipment)
                ->atPath('method')
                ->addViolation();
        }

        if ($shipment->hasParcels() && !$gateway->supports(Gateway\GatewayInterface::CAPABILITY_PARCEL)) {
            $this
                ->context
                ->buildViolation($constraint->method_does_not_support_parcel)
                ->atPath('method')
                ->addViolation();
        }

        if (0 < $shipment->getWeight() && $shipment->hasParcels()) {
            $this
                ->context
                ->buildViolation($constraint->weight_or_parcels_but_not_both)
                ->setInvalidValue($shipment->getWeight())
                ->atPath('weight')
                ->addViolation();
        }

        if (0 < $shipment->getValorization() && $shipment->hasParcels()) {
            $this
                ->context
                ->buildViolation($constraint->valorization_or_parcels_but_not_both)
                ->setInvalidValue($shipment->getWeight())
                ->atPath('valorization')
                ->addViolation();
        }

        if (1 === $shipment->getParcels()->count()) {
            $this
                ->context
                ->buildViolation($constraint->at_least_two_parcels_or_none)
                ->atPath('parcels')
                ->addViolation();
        }

        if ($shipment->isReturn() && $shipment->isAutoInvoice() && !$shipment->getSale()->isSample()) {
            if (null === $shipment->getCreditMethod()) {
                $this
                    ->context
                    ->buildViolation($constraint->credit_method_is_required)
                    ->atPath('creditMethod')
                    ->addViolation();
            }
        } elseif (null !== $shipment->getCreditMethod()) {
            $this
                ->context
                ->buildViolation($constraint->credit_method_must_be_null)
                ->atPath('creditMethod')
                ->addViolation();
        }
    }
}
