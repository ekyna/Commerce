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
    public $null_sale_item       = 'ekyna_commerce.invoice_line.null_sale_item';
    public $null_sale_adjustment = 'ekyna_commerce.invoice_line.null_sale_adjustment';
    public $empty_designation    = 'ekyna_commerce.invoice_line.empty_designation';
    public $hierarchy_integrity  = 'ekyna_commerce.invoice_line.hierarchy_integrity';
    public $invoiceable_overflow = 'ekyna_commerce.invoice_line.invoiceable_overflow';
    public $shipped_miss_match   = 'ekyna_commerce.invoice_line.shipped_miss_match';
    public $creditable_overflow  = 'ekyna_commerce.invoice_line.creditable_overflow';
    public $returned_miss_match  = 'ekyna_commerce.invoice_line.returned_miss_match';


    /**
     * @inheritDoc
     */
    public function getTargets()
    {
        return static::CLASS_CONSTRAINT;
    }
}
