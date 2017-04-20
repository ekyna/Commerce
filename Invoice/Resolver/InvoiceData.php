<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Invoice\Resolver;

use Decimal\Decimal;
use Ekyna\Component\Commerce\Invoice\Model\InvoiceInterface;

/**
 * Class InvoiceData
 * @package Ekyna\Component\Commerce\Invoice\Resolver
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class InvoiceData
{
    public InvoiceInterface $invoice;
    public bool             $credit;
    public Decimal          $total;
    public Decimal          $realTotal;

    public function __construct(InvoiceInterface $invoice, bool $credit, Decimal $total, Decimal $realTotal)
    {
        $this->invoice = $invoice;
        $this->credit = $credit;
        $this->total = $total;
        $this->realTotal = $realTotal;
    }
}
