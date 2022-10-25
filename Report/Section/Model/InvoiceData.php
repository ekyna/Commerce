<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Report\Section\Model;

use Decimal\Decimal;

/**
 * Class InvoiceData
 * @package Ekyna\Component\Commerce\Report\Section\Model
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class InvoiceData
{
    public function __construct(
        public Decimal $good = new Decimal(0),
        public Decimal $shipment = new Decimal(0),
        public Decimal $discount = new Decimal(0),
    ) {
    }
}
