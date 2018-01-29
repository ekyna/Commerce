<?php

namespace Ekyna\Component\Commerce\Invoice\Resolver;

use Ekyna\Component\Commerce\Common\Resolver\StateResolverInterface;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Invoice\Calculator\InvoiceCalculatorInterface;
use Ekyna\Component\Commerce\Invoice\Model\InvoiceStates;
use Ekyna\Component\Commerce\Invoice\Model\InvoiceSubjectInterface;
use Ekyna\Component\Commerce\Payment\Model\PaymentStates;

/**
 * Class InvoiceSubjectStateResolver
 * @package Ekyna\Component\Commerce\Invoice\Resolver
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class InvoiceSubjectStateResolver implements StateResolverInterface
{
    /**
     * @var InvoiceCalculatorInterface
     */
    protected $calculator;


    /**
     * Constructor.
     *
     * @param InvoiceCalculatorInterface $calculator
     */
    public function __construct(InvoiceCalculatorInterface $calculator)
    {
        $this->calculator = $calculator;
    }

    /**
     * @inheritdoc
     */
    public function resolve($subject)
    {
        if (!$subject instanceof InvoiceSubjectInterface) {
            throw new InvalidArgumentException("Expected instance of " . InvoiceSubjectInterface::class);
        }

        $quantities = $this->calculator->buildInvoiceQuantityMap($subject);
        if (0 === $itemsCount = count($quantities)) {
            return $this->setState($subject, InvoiceStates::STATE_NEW);
        }

        $partialCount = $invoicedCount = $creditedCount = 0;

        foreach ($quantities as $q) {
            // TODO Use packaging format
            // If invoiced greater than zero
            if (0 < $q['invoiced']) {
                // If total equals invoiced, item is fully invoiced
                //if ($q['total'] == $q['invoiced']) {
                if (0 === bccomp($q['total'], $q['invoiced'], 3)) {
                    $invoicedCount++;

                    // If invoiced equals credited, item is fully credited
                    if (0 === bccomp($q['invoiced'], $q['credited'], 3)) {
                        $creditedCount++;
                    }

                    continue;
                }

                // Item is partially invoiced
                $partialCount++;
            }
        }

        // TODO Assert sale's shipment and discounts are invoiced

        // If all fully credited
        if ($creditedCount == $itemsCount) {
            return $this->setState($subject, InvoiceStates::STATE_CREDITED);
        }

        // If all fully invoiced
        if ($invoicedCount == $itemsCount) {
            return $this->setState($subject, InvoiceStates::STATE_COMPLETED);
        }

        // If some partially invoiced
        if (0 < $partialCount || 0 < $invoicedCount) {
            return $this->setState($subject, InvoiceStates::STATE_PARTIAL);
        }

        /** @var \Ekyna\Component\Commerce\Common\Model\SaleInterface $subject */
        if (in_array($subject->getPaymentState(), PaymentStates::getCanceledStates(), true)) {
            return $this->setState($subject, InvoiceStates::STATE_CANCELED);
        }

        return $this->setState($subject, InvoiceStates::STATE_PENDING);
    }

    /**
     * Sets the shipment state.
     *
     * @param InvoiceSubjectInterface $subject
     * @param string                   $state
     *
     * @return bool Whether the shipment state has been updated.
     */
    protected function setState(InvoiceSubjectInterface $subject, $state)
    {
        if ($state !== $subject->getInvoiceState()) {
            $subject->setInvoiceState($state);

            return true;
        }

        return false;
    }
}
