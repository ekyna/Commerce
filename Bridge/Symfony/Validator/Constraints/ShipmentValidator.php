<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Bridge\Symfony\Validator\Constraints;

use Ekyna\Component\Commerce\Order\Model\OrderInterface;
use Ekyna\Component\Commerce\Order\Model\OrderStates;
use Ekyna\Component\Commerce\Shipment\Gateway;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentInterface;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentStates;
use Ekyna\Component\Commerce\Shipment\ShipmentUtil;
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
    public function __construct(
        private readonly Gateway\GatewayRegistryInterface $registry
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

        if (!$value instanceof ShipmentInterface) {
            throw new UnexpectedTypeException($value, ShipmentInterface::class);
        }
        if (!$constraint instanceof Shipment) {
            throw new UnexpectedTypeException($constraint, Shipment::class);
        }

        $sale = $value->getSale();

        /**
         * Shipment can't have a stockable state if order is not in a stockable state
         */
        if (ShipmentStates::isStockableState($value, false)) {
            // Only orders are supported.
            if (!$sale instanceof OrderInterface) {
                throw new UnexpectedTypeException($sale, OrderInterface::class);
            }
            if (!OrderStates::isStockableState($sale->getState())) {
                $this
                    ->context
                    ->buildViolation($constraint->shipped_state_denied)
                    ->setInvalidValue($value->getState())
                    ->atPath('state')
                    ->addViolation();
            }
        }

        $method = $value->getMethod();

        $gateway = $this->registry->getGateway($method->getGatewayName());

        // Disallow virtual platform if shipment contains at least one physical item
        if (
            $gateway->supports(Gateway\GatewayInterface::CAPABILITY_VIRTUAL)
            && ShipmentUtil::hasPhysicalItem($value)
        ) {
            $this
                ->context
                ->buildViolation($constraint->method_is_virtual_shipment_is_physical)
                ->atPath('method')
                ->addViolation();

            return;
        }

        // Return or shipment capability
        if ($value->isReturn() && !$gateway->supports(Gateway\GatewayInterface::CAPABILITY_RETURN)) {
            $this
                ->context
                ->buildViolation($constraint->method_does_not_support_return)
                ->atPath('method')
                ->addViolation();

            return;
        }

        if (!$value->isReturn() && !$gateway->supports(Gateway\GatewayInterface::CAPABILITY_SHIPMENT)) {
            $this
                ->context
                ->buildViolation($constraint->method_does_not_support_shipment)
                ->atPath('method')
                ->addViolation();

            return;
        }

        // Parcel capability
        if ($value->hasParcels() && !$gateway->supports(Gateway\GatewayInterface::CAPABILITY_PARCEL)) {
            $this
                ->context
                ->buildViolation($constraint->method_does_not_support_parcel)
                ->atPath('method')
                ->addViolation();

            return;
        }

        // Parcels count
        if (1 === $value->getParcels()->count()) {
            $this
                ->context
                ->buildViolation($constraint->at_least_two_parcels_or_none)
                ->atPath('parcels')
                ->addViolation();

            return;
        }

        if ($value->hasParcels()) {
            // Weight and parcels
            if (0 < $weight = $value->getWeight()) {
                $this
                    ->context
                    ->buildViolation($constraint->weight_or_parcels_but_not_both)
                    ->setInvalidValue($weight)
                    ->atPath('weight')
                    ->addViolation();

                return;
            }

            // Valorization and parcels
            if (0 < $valorization = $value->getValorization()) {
                $this
                    ->context
                    ->buildViolation($constraint->valorization_or_parcels_but_not_both)
                    ->setInvalidValue($valorization)
                    ->atPath('valorization')
                    ->addViolation();

                return;
            }
        }

        // Max weight
        if (0 < $maxWeight = $gateway->getMaxWeight()) {
            if ($value->hasParcels()) {
                $index = 0;
                foreach ($value->getParcels() as $parcel) {
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
            } elseif ($maxWeight < $weight = $value->getWeight()) {
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
        $address = $gateway->getAddressResolver()->resolveReceiverAddress($value, true);
        if ($gateway->requires(Gateway\GatewayInterface::REQUIREMENT_MOBILE)) {
            if (is_null($address->getMobile())) {
                $this->context
                    ->buildViolation($constraint->method_requires_mobile)
                    ->addViolation();
            }
        }
    }
}
