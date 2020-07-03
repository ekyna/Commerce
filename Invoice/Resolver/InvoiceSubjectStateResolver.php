<?php

namespace Ekyna\Component\Commerce\Invoice\Resolver;

use Ekyna\Component\Commerce\Common\Resolver\AbstractStateResolver;
use Ekyna\Component\Commerce\Exception\UnexpectedTypeException;
use Ekyna\Component\Commerce\Invoice\Calculator\InvoiceSubjectCalculatorInterface;
use Ekyna\Component\Commerce\Invoice\Model\InvoiceStates;
use Ekyna\Component\Commerce\Invoice\Model\InvoiceSubjectInterface;
use Ekyna\Component\Commerce\Payment\Model\PaymentStates;
use Ekyna\Component\Commerce\Payment\Model\PaymentSubjectInterface;

/**
 * Class InvoiceSubjectStateResolver
 * @package Ekyna\Component\Commerce\Invoice\Resolver
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class InvoiceSubjectStateResolver extends AbstractStateResolver
{
    /**
     * @var InvoiceSubjectCalculatorInterface
     */
    protected $invoiceCalculator;


    /**
     * Constructor.
     *
     * @param InvoiceSubjectCalculatorInterface $invoiceCalculator
     */
    public function __construct(InvoiceSubjectCalculatorInterface $invoiceCalculator)
    {
        $this->invoiceCalculator = $invoiceCalculator;
    }

    /**
     * @inheritDoc
     *
     * @param InvoiceSubjectInterface $subject
     */
    public function resolve(object $subject): bool
    {
        $this->supports($subject);

        $state = $this->resolveState($subject);

        if ($state !== $subject->getInvoiceState()) {
            $subject->setInvoiceState($state);

            return true;
        }

        return false;
    }

    /**
     * @inheritDoc
     *
     * @param InvoiceSubjectInterface $subject
     */
    protected function resolveState(object $subject): string
    {
        $quantities = $this->invoiceCalculator->buildInvoiceQuantityMap($subject);
        if (0 === $itemsCount = count($quantities)) {
            return InvoiceStates::STATE_NEW;
        }

        $partialCount = $invoicedCount = $creditedCount = 0;

        foreach ($quantities as $q) {
            // TODO Use packaging format
            // If invoiced greater than zero
            if (0 < $q['invoiced']) {
                // If invoiced is greater or equals total
                if (0 <= bccomp($q['invoiced'], $q['total'], 3)) {
                    // If invoiced equals credited, item is fully credited
                    if (0 === bccomp($q['invoiced'], $q['credited'], 3)) {
                        $creditedCount++;
                        continue;
                    }

                    // If total equals invoiced - credit, item is fully invoiced
                    if (0 === bccomp($q['total'], $q['invoiced'] - $q['credited'], 3)) {
                        $invoicedCount++;
                        continue;
                    }

                    // If shipped and credited, and shipped - returns equals invoiced - credit, item is fully invoiced
                    if (0 < $q['credited'] && 0 === bccomp($q['shipped'] - $q['returned'], $q['invoiced'] - $q['credited'], 3)) {
                        $invoicedCount++;
                        continue;
                    }
                }

                // Item is partially invoiced
                $partialCount++;
            }
        }

        // TODO Assert sale's shipment and discounts are invoiced

        // If all fully credited
        if ($creditedCount === $itemsCount) {
            return InvoiceStates::STATE_CREDITED;
        }

        // If all fully invoiced
        if ($invoicedCount === $itemsCount) {
            return InvoiceStates::STATE_COMPLETED;
        }

        // If some partially invoiced
        if (0 < $partialCount || 0 < $invoicedCount) {
            return InvoiceStates::STATE_PARTIAL;
        }

        // CANCELED If subject has payment(s) and has canceled state
        if ($subject instanceof PaymentSubjectInterface) {
            if (in_array($subject->getPaymentState(), PaymentStates::getCanceledStates(), true)) {
                return InvoiceStates::STATE_CANCELED;
            }
        }

        // NEW by default
        return InvoiceStates::STATE_NEW;
    }

    /**
     * @inheritDoc
     */
    protected function supports(object $subject): void
    {
        if (!$subject instanceof InvoiceSubjectInterface) {
            throw new UnexpectedTypeException($subject, InvoiceSubjectInterface::class);
        }
    }
}
