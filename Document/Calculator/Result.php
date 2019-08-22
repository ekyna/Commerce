<?php

namespace Ekyna\Component\Commerce\Document\Calculator;

use Ekyna\Component\Commerce\Common\Model\Amount;

/**
 * Class Result
 * @package Ekyna\Component\Commerce\Document\Calculator
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class Result
{
    /**
     * @var Amount
     */
    private $gross;

    /**
     * @var float
     */
    private $shipment;

    /**
     * @var Amount
     */
    private $final;


    /**
     * Constructor.
     *
     * @param Amount $gross
     * @param float  $shipment
     * @param Amount $final
     */
    public function __construct(Amount $gross, float $shipment, Amount $final)
    {
        $this->gross = $gross;
        $this->shipment = $shipment;
        $this->final = $final;
    }

    /**
     * Returns the gross.
     *
     * @return Amount
     */
    public function getGross(): Amount
    {
        return $this->gross;
    }

    /**
     * Returns the shipment.
     *
     * @return float
     */
    public function getShipment(): float
    {
        return $this->shipment;
    }

    /**
     * Returns the final.
     *
     * @return Amount
     */
    public function getFinal(): Amount
    {
        return $this->final;
    }
}
