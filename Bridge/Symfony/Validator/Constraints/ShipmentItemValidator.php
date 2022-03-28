<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Bridge\Symfony\Validator\Constraints;

use Ekyna\Component\Commerce\Shipment\Model\ShipmentItemInterface;
use Ekyna\Component\Commerce\Shipment\Resolver\AvailabilityResolverFactory;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * Class ShipmentItemValidator
 * @package Ekyna\Component\Commerce\Bridge\Symfony\Validator\Constraints
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ShipmentItemValidator extends ConstraintValidator
{
    private AvailabilityResolverFactory $resolverFactory;

    public function __construct(AvailabilityResolverFactory $resolverFactory)
    {
        $this->resolverFactory = $resolverFactory;
    }

    /**
     * @inheritDoc
     */
    public function validate($value, Constraint $constraint)
    {
        if (null === $value) {
            return;
        }

        if (!$value instanceof ShipmentItemInterface) {
            throw new UnexpectedTypeException($value, ShipmentItemInterface::class);
        }
        if (!$constraint instanceof ShipmentItem) {
            throw new UnexpectedTypeException($constraint, ShipmentItem::class);
        }

        // Check parent/quantity integrity
        $saleItem = $value->getSaleItem();
        $shipment = $value->getShipment();

        if ($saleItem->isPrivate() && !$value->getQuantity()->rem($saleItem->getQuantity())->isZero()) {
            $this
                ->context
                ->buildViolation($constraint->parent_quantity_integrity, [
                    '%multiple%' => $saleItem->getQuantity(),
                ])
                ->setInvalidValue($value->getQuantity())
                ->atPath('quantity')
                ->addViolation();

            return;
        }

        if (null === $availability = $value->getAvailability()) {
            $availability = $this
                ->resolverFactory
                ->createWithShipment($shipment)
                ->resolveSaleItem($saleItem);
        }

        $max = $availability->getAssigned();

        // Return shipment case
        if ($shipment->isReturn()) {
            $max = $availability->getExpected();

            if ($max < $value->getQuantity()) {
                $this
                    ->context
                    ->buildViolation($constraint->returnable_overflow, [
                        '%max%' => $max,
                    ])
                    ->setInvalidValue($value->getQuantity())
                    ->atPath('quantity')
                    ->addViolation();
            }

            return;
        }

        // Regular shipment case
        if ($max < $value->getQuantity()) {
            $this
                ->context
                ->buildViolation($constraint->shippable_overflow, [
                    '%max%' => $max,
                ])
                ->setInvalidValue($value->getQuantity())
                ->atPath('quantity')
                ->addViolation();
        }
    }
}
