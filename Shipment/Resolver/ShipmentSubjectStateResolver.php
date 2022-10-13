<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Shipment\Resolver;

use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Common\Resolver\AbstractStateResolver;
use Ekyna\Component\Commerce\Exception\UnexpectedTypeException;
use Ekyna\Component\Commerce\Shipment\Calculator\ShipmentSubjectCalculatorInterface;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentStates;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentSubjectInterface;

use function count;

/**
 * Class ShipmentStateResolver
 * @package Ekyna\Component\Commerce\Shipment\Resolver
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ShipmentSubjectStateResolver extends AbstractStateResolver
{
    public function __construct(
        protected readonly ShipmentSubjectCalculatorInterface $calculator
    ) {
    }

    /**
     * @inheritDoc
     *
     * @param ShipmentSubjectInterface $subject
     */
    public function resolve(object $subject): bool
    {
        $this->supports($subject);

        $state = $this->resolveState($subject);

        if ($state !== $subject->getShipmentState()) {
            $subject->setShipmentState($state);

            return true;
        }

        return false;
    }

    /**
     * @inheritDoc
     *
     * @param ShipmentSubjectInterface $subject
     */
    protected function resolveState(object $subject): string
    {
        // If subject has at least one preparation shipment
        foreach ($subject->getShipments(true) as $shipment) {
            if ($shipment->getState() === ShipmentStates::STATE_PREPARATION) {
                return ShipmentStates::STATE_PREPARATION;
            }
            if ($shipment->getState() === ShipmentStates::STATE_READY) {
                return ShipmentStates::STATE_READY;
            }
        }

        // If subject has at least one pending return
        foreach ($subject->getShipments(false) as $return) {
            if ($return->getState() === ShipmentStates::STATE_PENDING) {
                return ShipmentStates::STATE_PENDING;
            }
        }

        $quantities = $this->calculator->buildShipmentQuantityMap($subject);
        if (0 === $itemsCount = count($quantities)) {
            return ShipmentStates::STATE_NEW;
        }

        $sample = $released = false;
        if ($subject instanceof SaleInterface) {
            $sample = $subject->isSample();
            $released = $subject->isReleased();
        }

        $partialCount = $shippedCount = $returnedCount = $canceledCount = 0;

        foreach ($quantities as $q) {
            if ($sample) {
                if ($q['shipped']->isZero()) {
                    continue;
                }

                // Released sale is automatically fully shipped
                if ($released || $q['shipped'] >= $q['sold']) {
                    $shippedCount++;
                    if ($q['shipped']->equals($q['returned'])) {
                        $returnedCount++;
                    }

                    continue;
                }

                $partialCount++;

                continue;
            }

            if (!$q['invoiced']) {
                // Non-invoiced orders does not affect sold quantity (no credit equivalent for return)
                $q['sold'] = $q['sold']->sub($q['returned']);
            }

            // TODO Use packaging format
            // If shipped greater than zero
            if (0 < $q['shipped']) {
                // If shipped is greater than sold, item is fully shipped
                if ($q['shipped']->sub($q['returned'])->equals($q['sold'])) {
                    $shippedCount++;

                    // If shipped equals returned, item is fully returned
                    if ($q['shipped']->equals($q['returned'])) {
                        $returnedCount++;
                    }

                    continue;
                }

                // Item is partially shipped
                if (0 < $q['sold']) {
                    $partialCount++;
                }

                continue;
            }

            if ($q['sold']->isZero()) {
                // Item is canceled
                $canceledCount++;
            }
        }

        // CANCELED If fully canceled
        if ($canceledCount === $itemsCount) {
            return ShipmentStates::STATE_CANCELED;
        }

        // RETURNED If fully returned/canceled
        if ($returnedCount + $canceledCount === $itemsCount) {
            return ShipmentStates::STATE_RETURNED;
        }

        // COMPLETED If fully shipped/canceled
        if ($shippedCount + $canceledCount === $itemsCount) {
            return ShipmentStates::STATE_COMPLETED;
        }

        // PARTIAL If partially shipped
        if (0 < $partialCount || 0 < $shippedCount) {
            return ShipmentStates::STATE_PARTIAL;
        }

        // NEW by default
        return ShipmentStates::STATE_NEW;
    }

    protected function supports(object $subject): void
    {
        if (!$subject instanceof ShipmentSubjectInterface) {
            throw new UnexpectedTypeException($subject, ShipmentSubjectInterface::class);
        }
    }
}
