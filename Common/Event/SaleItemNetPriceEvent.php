<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Common\Event;

use Decimal\Decimal;

/**
 * Class SaleItemNetPriceEvent
 * @package Ekyna\Component\Commerce\Common\Event
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SaleItemNetPriceEvent extends SaleItemEvent
{
    private ?Decimal $netPrice = null;

    public function getNetPrice(): ?Decimal
    {
        return $this->netPrice;
    }

    public function setNetPrice(?Decimal $netPrice): void
    {
        $this->netPrice = $netPrice;
    }
}
