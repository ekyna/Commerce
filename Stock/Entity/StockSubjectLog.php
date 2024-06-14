<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Stock\Entity;

use DateTimeInterface;
use Decimal\Decimal;
use Ekyna\Component\Commerce\Stock\Model\StockLogTypeEnum;
use Ekyna\Component\Commerce\Stock\Model\StockSubjectInterface;

/**
 * Class StockSubjectLog
 * @package Ekyna\Component\Commerce\Stock\Entity
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class StockSubjectLog
{
    public function __construct(
        public readonly StockSubjectInterface $subject,
        public readonly StockLogTypeEnum      $type,
        public readonly DateTimeInterface     $date,
        public readonly Decimal               $quantity,
        public readonly string                $source,
    ) {
    }
}
