<?php

namespace Ekyna\Component\Commerce\Bridge\Symfony\Validator\Constraints;

use Ekyna\Component\Commerce\Exception\ValidationFailedException;
use Ekyna\Component\Commerce\Invoice\Model\InvoiceInterface;
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

        // At least one line or item
        if (0 === count($invoice->getLines()) && 0 === count($invoice->getItems())) {
            $this
                ->context
                ->buildViolation($constraint->at_least_one_line_or_item)
                ->atPath('items')
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

            // Credit <-> Return or Invoice <-> Shipment
            if ($invoice->isCredit() xor $shipment->isReturn()) {
                throw new ValidationFailedException();
            }
        }
    }
}
