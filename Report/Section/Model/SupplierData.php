<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Report\Section\Model;

use Decimal\Decimal;

/**
 * Class SupplierData
 * @package Ekyna\Component\Commerce\Report\Section\Model
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class SupplierData
{
    public Decimal $saleGoodCost;
    public Decimal $saleSupplyCost;
    public Decimal $saleRevenue;
    public Decimal $orderGoodCost;
    public Decimal $orderSupplyCost;

    public function __construct()
    {
        $this->saleGoodCost = new Decimal(0);
        $this->saleSupplyCost = new Decimal(0);
        $this->saleRevenue = new Decimal(0);
        $this->orderGoodCost = new Decimal(0);
        $this->orderSupplyCost = new Decimal(0);
    }

    public function merge(SupplierData $data): void
    {
        $this->saleGoodCost += $data->saleGoodCost;
        $this->saleSupplyCost += $data->saleSupplyCost;
        $this->saleRevenue += $data->saleRevenue;
        $this->orderGoodCost += $data->orderGoodCost;
        $this->orderSupplyCost += $data->orderSupplyCost;
    }
}
