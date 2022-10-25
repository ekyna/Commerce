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
    public Decimal $goodCost;
    public Decimal $supplyCost;
    public Decimal $sale;
    public Decimal $order;

    public function __construct()
    {
        $this->goodCost = new Decimal(0);
        $this->supplyCost = new Decimal(0);
        $this->sale = new Decimal(0);
        $this->order = new Decimal(0);
    }

    public function merge(SupplierData $data): void
    {
        $this->goodCost += $data->goodCost;
        $this->supplyCost += $data->supplyCost;
        $this->sale += $data->sale;
        $this->order += $data->order;
    }
}
