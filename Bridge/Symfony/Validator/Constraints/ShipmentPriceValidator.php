<?php

namespace Ekyna\Component\Commerce\Bridge\Symfony\Validator\Constraints;

use Ekyna\Component\Commerce\Shipment\Gateway\GatewayRegistryInterface;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentPriceInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\InvalidArgumentException;

/**
 * Class ShipmentPriceValidator
 * @package Ekyna\Component\Commerce\Bridge\Symfony\Validator\Constraints
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ShipmentPriceValidator extends ConstraintValidator
{
    /**
     * @var GatewayRegistryInterface
     */
    private $gatewayRegistry;


    /**
     * Constructor.
     *
     * @param GatewayRegistryInterface $gatewayRegistry
     */
    public function __construct(GatewayRegistryInterface $gatewayRegistry)
    {
        $this->gatewayRegistry = $gatewayRegistry;
    }

    /**
     * @inheritDoc
     */
    public function validate($value, Constraint $constraint)
    {
        if (!$value instanceof ShipmentPriceInterface) {
            throw new InvalidArgumentException("Expected instance of " . ShipmentPriceInterface::class);
        }
        if (!$constraint instanceof ShipmentPrice) {
            throw new InvalidArgumentException("Expected instance of " . ShipmentPrice::class);
        }

        if (null === $method = $value->getMethod()) {
            return;
        }

        $gateway = $this->gatewayRegistry->getGateway($method->getGatewayName());

        if (0 >= $maxWeight = $gateway->getMaxWeight()) {
            return;
        }

        if ($value->getWeight() > $maxWeight) {
            $this
                ->context
                ->buildViolation($constraint->max_weight, [
                    '%max%' => $maxWeight,
                ])
                ->atPath('weight')
                ->addViolation();
        }
    }
}
