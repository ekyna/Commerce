<?php

namespace Ekyna\Component\Commerce\Bridge\Symfony\Validator\Constraints;

use Ekyna\Component\Commerce\Document\Model\DocumentLineTypes;
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

        // If credit, must have a payment method
        if (InvoiceTypes::isCredit($invoice) && null === $invoice->getPaymentMethod()) {
            $this
                ->context
                ->buildViolation($constraint->null_credit_method)
                ->atPath('paymentMethod')
                ->addViolation();
        }

        // Can't have no good lines
        if (empty($invoice->getLinesByType(DocumentLineTypes::TYPE_GOOD))) {
            $this
                ->context
                ->buildViolation($constraint->empty_good_lines)
                ->atPath('lines')
                ->addViolation();

            return;
        }

        // [ Invoice <-> Sale <-> Shipment ] integrity
        if (null !== $shipment = $invoice->getShipment()) {
            if ($invoice->getSale() !== $shipment->getSale()) {
                $this
                    ->context
                    ->buildViolation($constraint->hierarchy_integrity)
                    ->setInvalidValue($shipment)
                    ->atPath('shipment')
                    ->addViolation();

                return;
            }
        }
    }
}
