<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Stock\Model;

use Decimal\Decimal;

/**
 * Class StockAdjustmentData
 * @package Ekyna\Component\Commerce\Stock\Model
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class StockAdjustmentData
{
    public function __construct(
        public readonly StockSubjectInterface $subject,
        public Decimal                        $quantity = new Decimal(0),
        public string                         $reason = StockAdjustmentReasons::REASON_DEBIT,
        public ?string                        $note = null
    ) {
    }
}
