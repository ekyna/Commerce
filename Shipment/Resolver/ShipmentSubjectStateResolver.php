<?php

namespace Ekyna\Component\Commerce\Shipment\Resolver;

use Ekyna\Component\Commerce\Common\Resolver\AbstractStateResolver;
use Ekyna\Component\Commerce\Exception\UnexpectedTypeException;
use Ekyna\Component\Commerce\Invoice\Model\InvoiceStates;
use Ekyna\Component\Commerce\Invoice\Model\InvoiceSubjectInterface;
use Ekyna\Component\Commerce\Payment\Model\PaymentStates;
use Ekyna\Component\Commerce\Payment\Model\PaymentSubjectInterface;
use Ekyna\Component\Commerce\Shipment\Calculator\ShipmentSubjectCalculatorInterface;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentStates;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentSubjectInterface;

use function bccomp;

/**
 * Class ShipmentStateResolver
 * @package Ekyna\Component\Commerce\Shipment\Resolver
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ShipmentSubjectStateResolver extends AbstractStateResolver
{
    /**
     * @var ShipmentSubjectCalculatorInterface
     */
    protected $calculator;


    /**
     * Constructor.
     *
     * @param ShipmentSubjectCalculatorInterface $calculator
     */
    public function __construct(ShipmentSubjectCalculatorInterface $calculator)
    {
        $this->calculator = $calculator;
    }

    /**
     * @inheritdoc
     *
     * @param ShipmentSubjectInterface
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
        // If has at least one preparation shipment
        foreach ($subject->getShipments(true) as $shipment) {
            if ($shipment->getState() === ShipmentStates::STATE_PREPARATION) {
                return ShipmentStates::STATE_PREPARATION;
            }
        }

        // If has at least one pending return
        foreach ($subject->getShipments(false) as $return) {
            if ($return->getState() === ShipmentStates::STATE_PENDING) {
                return ShipmentStates::STATE_PENDING;
            }
        }

        $quantities = $this->calculator->buildShipmentQuantityMap($subject);
        if (0 === $itemsCount = count($quantities)) {
            return ShipmentStates::STATE_NEW;
        }

        $partialCount = $shippedCount = $returnedCount = $canceledCount = 0;

        foreach ($quantities as $q) {
            // TODO Use packaging format
            // If shipped greater than zero
            if (0 < $q['shipped']) {
                // If shipped is greater than sold, item is fully shipped
                if (0 <= bccomp($q['shipped'] - $q['returned'], $q['sold'], 3)) {
                    $shippedCount++;

                    // If shipped equals returned, item is fully returned
                    if (0 === bccomp($q['shipped'], $q['returned'], 3)) {
                        $returnedCount++;
                    }

                    continue;
                }

                // Item is partially shipped
                if (1 === bccomp($q['sold'], 0, 3)) {
                    $partialCount++;
                }

                continue;
            }

            if (0 === bccomp($q['sold'], 0, 3)) {
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

        // CANCELED If subject has invoice(s) and is fully credited
        if ($subject instanceof InvoiceSubjectInterface) {
            if ($subject->getInvoiceState() === InvoiceStates::STATE_CREDITED) {
                return ShipmentStates::STATE_CANCELED;
            }
        }

        // CANCELED If subject has payment(s) and has canceled state
        if ($subject instanceof PaymentSubjectInterface) {
            if (in_array($subject->getPaymentState(), PaymentStates::getCanceledStates(), true)) {
                return ShipmentStates::STATE_CANCELED;
            }
        }

        // NEW by default
        return ShipmentStates::STATE_NEW;
    }

    /**
     * @inheritDoc
     */
    protected function supports(object $subject): void
    {
        if (!$subject instanceof ShipmentSubjectInterface) {
            throw new UnexpectedTypeException($subject, ShipmentSubjectInterface::class);
        }
    }
}
