<?php

namespace Ekyna\Component\Commerce\Bridge\Symfony\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * Class InvoiceLine
 * @package Ekyna\Component\Commerce\Bridge\Symfony\Validator\Constraints
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class InvoiceLine extends Constraint
{
    public $shipment_is_not_return = 'ekyna_commerce.invoice_line.shipment_is_not_return';
    public $sale_item_and_shipment_item_miss_match = 'ekyna_commerce.invoice_line.sale_item_and_shipment_item_miss_match';
    public $quantity_is_greater_than_returned = 'ekyna_commerce.invoice_line.quantity_is_greater_than_returned';
    public $sale_and_invoice_miss_match = 'ekyna_commerce.invoice_line.sale_and_invoice_miss_match';
    public $quantity_is_greater_than_invoiceable = 'ekyna_commerce.invoice_line.quantity_is_greater_than_invoiceable';


    /**
     * @inheritDoc
     */
    public function getTargets()
    {
        return static::CLASS_CONSTRAINT;
    }
}
