<?php

namespace Ekyna\Component\Commerce\Bridge\Symfony\Validator\Constraints;

use Ekyna\Component\Commerce\Exception\ValidationFailedException;
use Ekyna\Component\Commerce\Invoice\Model\InvoiceInterface;
use Ekyna\Component\Commerce\Invoice\Model\InvoiceTypes;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * Class InvoiceValidator
 * @package Ekyna\Component\Commerce\Bridge\Symfony\Validator\Constraints
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class InvoiceValidator extends ConstraintValidator
{
    /**
     * @inheritDoc
     */
    public function validate($invoice, Constraint $constraint)
    {
        if (null === $invoice) {
            return;
        }

        if (!$invoice instanceof InvoiceInterface) {
            throw new UnexpectedTypeException($invoice, InvoiceInterface::class);
        }
        if (!$constraint instanceof Invoice) {
            throw new UnexpectedTypeException($constraint, Invoice::class);
        }

        // If credit and sale is paid, must have a payment method
        if (InvoiceTypes::isCredit($invoice) && $invoice->getSale()->isPaid() && is_null($invoice->getPaymentMethod())) {
            $this
                ->context
                ->buildViolation($constraint->null_credit_method)
                ->atPath('paymentMethod')
                ->addViolation();

            return;
        }

        try {
            $this->checkHierarchyIntegrity($invoice);
        } catch (ValidationFailedException $e) {
            $this
                ->context
                ->buildViolation($constraint->hierarchy_integrity)
                ->atPath('shipment')
                ->addViolation();
        }
    }

    /**
     * Check the hierarchy integrity.
     *
     * @param InvoiceInterface $invoice
     *
     * @throws ValidationFailedException
     */
    private function checkHierarchyIntegrity(InvoiceInterface $invoice)
    {
        // [ Invoice <-> Sale <-> Shipment ] integrity
        if (null !== $shipment = $invoice->getShipment()) {
            if ($invoice->getSale() !== $shipment->getSale()) {
                throw new ValidationFailedException();
            }

            // Credit <-> Return
            if (InvoiceTypes::isCredit($invoice) && !$shipment->isReturn()) {
                throw new ValidationFailedException();
            }

            // Invoice <-> Shipment
            if (InvoiceTypes::isInvoice($invoice) && $shipment->isReturn()) {
                throw new ValidationFailedException();
            }
        }
    }
}
