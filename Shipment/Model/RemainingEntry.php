<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Shipment\Model;

use Decimal\Decimal;
use Ekyna\Component\Commerce\Common\Model\SaleItemInterface;

/**
 * Class RemainingEntry
 * @package Ekyna\Component\Commerce\Shipment\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class RemainingEntry
{
    private SaleItemInterface $saleItem;
    private Decimal           $quantity;

    public function __construct(SaleItemInterface $saleItem, Decimal $quantity)
    {
        $this->saleItem = $saleItem;
        $this->quantity = $quantity;
    }

    public function getSaleItem(): SaleItemInterface
    {
        return $this->saleItem;
    }

    public function getQuantity(): Decimal
    {
        return $this->quantity;
    }

}
