<?php

namespace Ekyna\Component\Commerce\Bridge\Symfony\Validator\Constraints;

use Ekyna\Component\Commerce\Shipment\Gateway;
use Ekyna\Component\Commerce\Shipment\Model;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Class RelayPointValidator
 * @package Ekyna\Component\Commerce\Bridge\Symfony\Validator\Constraints
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class RelayPointValidator extends ConstraintValidator
{
    /**
     * @var Gateway\RegistryInterface
     */
    private $gatewayRegistry;

    /**
     * @var \Symfony\Component\PropertyAccess\PropertyAccessor
     */
    private $propertyAccessor;


    /**
     * Constructor.
     *
     * @param Gateway\RegistryInterface $gatewayRegistry
     */
    public function __construct(Gateway\RegistryInterface $gatewayRegistry)
    {
        $this->gatewayRegistry = $gatewayRegistry;
    }

    /**
     * @inheritDoc
     */
    public function validate($value, Constraint $constraint)
    {
        /** @var RelayPoint $constraint */
        $accessor = $this->getPropertyAccessor();

        /** @var Model\ShipmentMethodInterface $method */
        $method = $accessor->getValue($value, $constraint->shipmentMethodPath);
        /** @var Model\RelayPointInterface $point */
        $point = $accessor->getValue($value, $constraint->relayPointPath);

        if (null === $method) {
            if (null !== $point) {
                $this->context
                    ->buildViolation($constraint->relay_point_must_be_null)
                    ->atPath($constraint->relayPointPath)
                    ->addViolation();
            }

            return;
        }

        $gateway = $this->gatewayRegistry->getGateway($method->getGatewayName());

        // If gateway does not support relay point
        if (!$gateway->supports(Gateway\GatewayInterface::CAPABILITY_RELAY)) {
            if (null !== $point) {
                $this->context
                    ->buildViolation($constraint->relay_point_must_be_null)
                    ->atPath($constraint->relayPointPath)
                    ->addViolation();
            }

            return;
        }

        // Asserts that a relay point is set
        if (null === $point) {
            $this->context
                ->buildViolation($constraint->relay_point_is_required)
                ->atPath($constraint->relayPointPath)
                ->addViolation();

            return;
        }

        // Assert that the given relay point belongs to the gateway
        if ($point->getPlatform() !== $gateway->getPlatform()->getName()) {
            $this->context
                ->buildViolation($constraint->gateway_miss_match)
                ->atPath($constraint->relayPointPath)
                ->addViolation();
        }
    }

    /**
     * @return \Symfony\Component\PropertyAccess\PropertyAccessor
     */
    private function getPropertyAccessor()
    {
        if (null !== $this->propertyAccessor) {
            return $this->propertyAccessor;
        }

        return $this->propertyAccessor = PropertyAccess::createPropertyAccessor();
    }
}
