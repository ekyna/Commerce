<?php

namespace Ekyna\Component\Commerce\Shipment\Resolver;

use Ekyna\Component\Commerce\Common\Resolver\StateResolverInterface;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Shipment\Calculator\ShipmentCalculatorInterface;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentStates;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentSubjectInterface;

/**
 * Class ShipmentStateResolver
 * @package Ekyna\Component\Commerce\Shipment\Resolver
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ShipmentSubjectStateResolver implements StateResolverInterface
{
    /**
     * @var ShipmentCalculatorInterface
     */
    protected $calculator;


    /**
     * Constructor.
     *
     * @param ShipmentCalculatorInterface $calculator
     */
    public function __construct(ShipmentCalculatorInterface $calculator)
    {
        $this->calculator = $calculator;
    }

    /**
     * @inheritdoc
     */
    public function resolve($subject)
    {
        if (!$subject instanceof ShipmentSubjectInterface) {
            throw new InvalidArgumentException("Expected instance of " . ShipmentSubjectInterface::class);
        }

        $quantities = $this->calculator->buildShipmentQuantityMap($subject);
        if (0 === $itemsCount = count($quantities)) {
            return $this->setState($subject, ShipmentStates::STATE_NONE);
        }

        if (!$subject->hasShipments()) {
            return $this->setState($subject, ShipmentStates::STATE_PENDING);
        }

        $partialCount = $shippedCount = $returnedCount = $canceledCount = 0;

        foreach ($quantities as $q) {
            // TODO Use packaging format
            if ($q['sold'] == $q['canceled']) {
                $canceledCount++;
                continue;
            }

            // If returned equals sold minus canceled, item is fully returned
            if ($q['sold'] - $q['canceled'] == $q['returned']) {
                $returnedCount++;
                continue;
            }

            // If shipped equals sold minus canceled, item is fully shipped
            if ($q['sold'] - $q['canceled'] == $q['shipped']) {
                $shippedCount++;
                continue;
            }

            // If shipped greater than zero, item is partially shipped
            if (0 < $q['shipped']) {
                $partialCount++;
            }
        }

        // If all fully shipped
        if ($shippedCount == $itemsCount || $canceledCount == $itemsCount) {
            return $this->setState($subject, ShipmentStates::STATE_COMPLETED);
        }

        // If all fully returned
        if ($returnedCount == $itemsCount) {
            return $this->setState($subject, ShipmentStates::STATE_RETURNED);
        }

        // If some partially shipped
        if (0 < $partialCount || 0 < $shippedCount) {
            return $this->setState($subject, ShipmentStates::STATE_PARTIAL);
        }

        return $this->setState($subject, ShipmentStates::STATE_PENDING);
    }

    /**
     * Sets the shipment state.
     *
     * @param ShipmentSubjectInterface $subject
     * @param string                   $state
     *
     * @return bool Whether the shipment state has been updated.
     */
    protected function setState(ShipmentSubjectInterface $subject, $state)
    {
        if ($state !== $subject->getShipmentState()) {
            $subject->setShipmentState($state);

            return true;
        }

        return false;
    }
}
