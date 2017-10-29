<?php

namespace Ekyna\Component\Commerce\Common\View;

/**
 * Class SaleView
 * @package Ekyna\Component\Commerce\Common\View
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SaleView extends AbstractView
{
    /**
     * @var string
     */
    private $mode;

    /**
     * @var TotalView
     */
    private $gross;

    /**
     * @var TotalView
     */
    private $final;

    /**
     * @var LineView[]
     */
    private $items;

    /**
     * @var LineView[]
     */
    private $discounts;

    /**
     * @var LineView
     */
    private $shipment;

    /**
     * @var TaxView[]
     */
    private $taxes;


    /**
     * Constructor.
     *
     * @param string     $mode
     * @param TotalView  $gross
     * @param TotalView  $final
     * @param LineView[] $items
     * @param LineView[] $discounts
     * @param LineView   $shipment
     * @param TaxView[]  $taxes
     */
    public function __construct(
        $mode,
        TotalView $gross,
        TotalView $final,
        array $items,
        array $discounts,
        $shipment,
        array $taxes
    ) {
        $this->mode = $mode;
        $this->gross = $gross;
        $this->final = $final;
        $this->items = $items;
        $this->discounts = $discounts;
        $this->shipment = $shipment;
        $this->taxes = $taxes;
    }

    /**
     * Returns the gross total view.
     *
     * @return TotalView
     */
    public function getGross()
    {
        return $this->gross;
    }

    /**
     * Returns the final total view.
     * @return TotalView
     */
    public function getFinal()
    {
        return $this->final;
    }

    /**
     * Returns the items lines views.
     *
     * @return LineView[]
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     * Returns the discounts lines views.
     *
     * @return LineView[]
     */
    public function getDiscounts()
    {
        return $this->discounts;
    }

    /**
     * Returns the shipment line view.
     *
     * @return LineView|null
     */
    public function getShipment()
    {
        return $this->shipment;
    }

    /**
     * Returns the taxes views.
     *
     * @return TaxView[]
     */
    public function getTaxes()
    {
        return $this->taxes;
    }
}
