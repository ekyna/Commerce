<?php

namespace Ekyna\Component\Commerce\Bridge\Symfony\Validator\Constraints;

use Ekyna\Component\Commerce\Shipment\Calculator\QuantityCalculatorInterface;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentItemInterface;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentStates;
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
    /**
     * @var QuantityCalculatorInterface
     */
    private $quantityCalculator;


    /**
     * Constructor.
     *
     * @param QuantityCalculatorInterface $quantityCalculator
     */
    public function __construct(QuantityCalculatorInterface $quantityCalculator)
    {
        $this->quantityCalculator = $quantityCalculator;
    }

    /**
     * @inheritDoc
     */
    public function validate($item, Constraint $constraint)
    {
        if (null === $item) {
            return;
        }

        if (!$item instanceof ShipmentItemInterface) {
            throw new UnexpectedTypeException($item, ShipmentItemInterface::class);
        }
        if (!$constraint instanceof ShipmentItem) {
            throw new UnexpectedTypeException($constraint, ShipmentItem::class);
        }

        // Return shipment case
        if ($item->getShipment()->isReturn()) {
            $returnable = $this->quantityCalculator->calculateReturnableQuantity($item);

            if ($item->getQuantity() > $returnable) {
                $this
                    ->context
                    ->buildViolation($constraint->quantity_must_be_lower_than_or_equal_shipped, [
                        '%max%' => $returnable
                    ])
                    ->setInvalidValue($item->getQuantity())
                    ->atPath('quantity')
                    ->addViolation();

                return;
            }

            return;
        }

        // Regular shipment case

        $expected = $this->quantityCalculator->calculateShippableQuantity($item);
        $available = $this->quantityCalculator->calculateAvailableQuantity($item);

        if (ShipmentStates::isStockableState($item->getShipment()->getState()) && $available < $expected) {
            // Shipment item's quantity must be lower than or equals the shipment item's available quantity
            if ($item->getQuantity() > $available) {
                $this
                    ->context
                    ->buildViolation($constraint->quantity_must_be_lower_than_or_equal_available, [
                        '%max%' => $available
                    ])
                    ->setInvalidValue($item->getQuantity())
                    ->atPath('quantity')
                    ->addViolation();

                return;
            }
        }
        // Shipment item's quantity must be lower than or equals the shipment item's available expected
        elseif ($item->getQuantity() > $expected) {
            $this
                ->context
                ->buildViolation($constraint->quantity_must_be_lower_than_or_equal_expected, [
                    '%max%' => $expected
                ])
                ->setInvalidValue($item->getQuantity())
                ->atPath('quantity')
                ->addViolation();

            return;
        }
    }
}
