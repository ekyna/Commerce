<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Document\Calculator;

use Decimal\Decimal;
use Ekyna\Component\Commerce\Common\Model\Amount;

/**
 * Class Result
 * @package Ekyna\Component\Commerce\Document\Calculator
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class Result
{
    private Amount  $gross;
    private Decimal $shipment;
    private Amount  $final;

    public function __construct(Amount $gross, Decimal $shipment, Amount $final)
    {
        $this->gross = $gross;
        $this->shipment = $shipment;
        $this->final = $final;
    }

    public function getGross(): Amount
    {
        return $this->gross;
    }

    public function getShipment(): Decimal
    {
        return $this->shipment;
    }

    public function getFinal(): Amount
    {
        return $this->final;
    }
}
