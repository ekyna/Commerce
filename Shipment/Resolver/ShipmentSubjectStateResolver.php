<?php

namespace Ekyna\Component\Commerce\Shipment\Resolver;

use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Common\Resolver\StateResolverInterface;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Invoice\Model\InvoiceStates;
use Ekyna\Component\Commerce\Invoice\Model\InvoiceSubjectInterface;
use Ekyna\Component\Commerce\Payment\Model\PaymentStates;
use Ekyna\Component\Commerce\Payment\Model\PaymentSubjectInterface;
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
            return $this->setState($subject, ShipmentStates::STATE_NEW);
        }

        $sample = $subject instanceof SaleInterface ? $subject->isSample() : false;

        $partialCount = $shippedCount = $returnedCount = $canceledCount = 0;

        foreach ($quantities as $q) {
            // TODO Use packaging format
            // If shipped greater than zero
            if (0 < $q['shipped']) {
                $sold = $q['total'];
                $shipped = $q['shipped'];
                if (!$sample) {
                    $sold -= $q['credited'];
                    $shipped -= $q['returned'];
                }
                // If sold equals shipped, item is fully shipped
                if (0 === bccomp($sold, $shipped, 3)) {
                    $shippedCount++;

                    // If shipped equals returned, item is fully returned
                    //if ($q['shipped'] == $q['returned']) {
                    if (0 === bccomp($q['shipped'], $q['returned'], 3)) {
                        $returnedCount++;
                    }

                    continue;
                }

                // Item is partially shipped
                $partialCount++;
                continue;
            }
        }


        // RETURNED If all fully returned
        if ($returnedCount == $itemsCount) {
            return $this->setState($subject, ShipmentStates::STATE_RETURNED);
        }

        // COMPLETED If all fully shipped
        if ($shippedCount == $itemsCount) {
            return $this->setState($subject, ShipmentStates::STATE_COMPLETED);
        }

        // PARTIAL If some partially shipped
        if (0 < $partialCount || 0 < $shippedCount) {
            return $this->setState($subject, ShipmentStates::STATE_PARTIAL);
        }

        // CANCELED If subject has invoice(s) and is fully credited
        if ($subject instanceof InvoiceSubjectInterface) {
            if ($subject->getInvoiceState() === InvoiceStates::STATE_CREDITED) {
                return $this->setState($subject, ShipmentStates::STATE_CANCELED);
            }
        }

        // CANCELED If subject has payment(s) and has canceled state
        if ($subject instanceof PaymentSubjectInterface) {
            if (in_array($subject->getPaymentState(), PaymentStates::getCanceledStates(), true)) {
                return $this->setState($subject, ShipmentStates::STATE_CANCELED);
            }
        }

        // PENDING by default
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
