<?php

namespace Ekyna\Component\Commerce\Bridge\Symfony\Validator\Constraints;

use Ekyna\Component\Commerce\Order\Model\OrderInterface;
use Ekyna\Component\Commerce\Order\Model\OrderStates;
use Ekyna\Component\Commerce\Shipment\Gateway;
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
     * @var Gateway\GatewayRegistryInterface
     */
    private $registry;


    /**
     * Constructor.
     *
     * @param Gateway\GatewayRegistryInterface $gatewayRegistry
     */
    public function __construct(Gateway\GatewayRegistryInterface $gatewayRegistry)
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

        $sale = $shipment->getSale();

        /**
         * Shipment can't have a stockable state if order is not in a stockable state
         */
        if (ShipmentStates::isStockableState($shipment, false)) {
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

        // Return or shipment capability
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

        // Parcel capability
        if ($shipment->hasParcels() && !$gateway->supports(Gateway\GatewayInterface::CAPABILITY_PARCEL)) {
            $this
                ->context
                ->buildViolation($constraint->method_does_not_support_parcel)
                ->atPath('method')
                ->addViolation();
        }

        // Parcels count
        if (1 === $shipment->getParcels()->count()) {
            $this
                ->context
                ->buildViolation($constraint->at_least_two_parcels_or_none)
                ->atPath('parcels')
                ->addViolation();
        }
        if ($shipment->hasParcels()) {
            // Weight and parcels
            if (0 < $weight = $shipment->getWeight()) {
                $this
                    ->context
                    ->buildViolation($constraint->weight_or_parcels_but_not_both)
                    ->setInvalidValue($weight)
                    ->atPath('weight')
                    ->addViolation();
            }

            // Valorization and parcels
            if (0 < $valorization = $shipment->getValorization()) {
                $this
                    ->context
                    ->buildViolation($constraint->valorization_or_parcels_but_not_both)
                    ->setInvalidValue($valorization)
                    ->atPath('valorization')
                    ->addViolation();
            }
        }

        // Max weight
        if (0 < $maxWeight = $gateway->getMaxWeight()) {
            if ($shipment->hasParcels()) {
                $index = 0;
                foreach ($shipment->getParcels() as $parcel) {
                    if ($maxWeight < $weight = $parcel->getWeight()) {
                        $this
                            ->context
                            ->buildViolation($constraint->max_weight, [
                                '%max%' => $maxWeight,
                            ])
                            ->setInvalidValue($weight)
                            ->atPath("parcels[$index].weight")
                            ->addViolation();
                    }
                }
            } elseif ($maxWeight < $weight = $shipment->getWeight()) {
                $this
                    ->context
                    ->buildViolation($constraint->max_weight, [
                        '%max%' => $maxWeight,
                    ])
                    ->setInvalidValue($weight)
                    ->atPath('weight')
                    ->addViolation();
            }
        }

        // Mobile requirement
        $address = $gateway->getAddressResolver()->resolveReceiverAddress($shipment, true);
        if ($gateway->requires(Gateway\GatewayInterface::REQUIREMENT_MOBILE)) {
            if (is_null($address->getMobile())) {
                $this->context
                    ->buildViolation($constraint->method_requires_mobile)
                    ->addViolation();
            }
        }
    }
}
