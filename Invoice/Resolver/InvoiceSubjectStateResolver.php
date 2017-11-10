<?php

namespace Ekyna\Component\Commerce\Invoice\Resolver;

use Ekyna\Component\Commerce\Common\Resolver\StateResolverInterface;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Invoice\Calculator\InvoiceCalculatorInterface;
use Ekyna\Component\Commerce\Invoice\Model\InvoiceStates;
use Ekyna\Component\Commerce\Invoice\Model\InvoiceSubjectInterface;

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

        if (!$subject->hasInvoices() || 0 === $itemsCount = count($quantities)) {
            return $this->setState($subject, InvoiceStates::STATE_NEW);
        }

        $partialCount = $invoicedCount = $creditedCount = 0;

        foreach ($quantities as $q) {
            // TODO Use packaging format

            // If invoiced equals sold, item is fully invoiced
            if ($q['sold'] == $q['invoiced']) {
                $invoicedCount++;
                continue;
            }

            // If credited equals sold, item is fully credited
            if ($q['sold'] == $q['credited']) {
                $creditedCount++;
                continue;
            }

            // If invoiced greater than zero, item is partially invoiced
            if (0 < $q['invoiced']) {
                $partialCount++;
            }
        }

        // TODO Check sale's shipment and discounts

        // If all fully credited
        if ($creditedCount == $itemsCount) {
            return $this->setState($subject, InvoiceStates::STATE_CREDITED);
        }
        // Else if all fully invoiced
        elseif ($invoicedCount == $itemsCount) {
            return $this->setState($subject, InvoiceStates::STATE_INVOICED);
        }
        // Else if some partially invoiced
        elseif (0 < $partialCount) {
            return $this->setState($subject, InvoiceStates::STATE_PARTIAL);
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
