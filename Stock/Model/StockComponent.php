<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Stock\Model;

use Decimal\Decimal;

/**
 * Class StockComponent
 * @package Ekyna\Component\Commerce\Stock\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class StockComponent
{
    private StockSubjectInterface $subject;
    private Decimal               $quantity;

    public function __construct(StockSubjectInterface $subject, Decimal $quantity)
    {
        $this->subject = $subject;
        $this->quantity = $quantity;
    }

    public function getSubject(): StockSubjectInterface
    {
        return $this->subject;
    }

    public function getQuantity(): Decimal
    {
        return $this->quantity;
    }
}
