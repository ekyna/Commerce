<?php

namespace Ekyna\Component\Commerce\Common\View;

/**
 * Class Sale
 * @package Ekyna\Component\Commerce\Common\View
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class Sale
{
    /**
     * @var string
     */
    private $mode;

    /**
     * @var Total
     */
    private $gross;

    /**
     * @var Total
     */
    private $final;

    /**
     * @var Line[]
     */
    private $items;

    /**
     * @var Line[]
     */
    private $discounts;

    /**
     * @var Tax[]
     */
    private $taxes;


    /**
     * Constructor.
     *
     * @param string $mode
     * @param Total  $gross
     * @param Total  $final
     * @param Line[] $items
     * @param Line[] $discounts
     * @param Tax[]  $taxes
     */
    public function __construct($mode, Total $gross, Total $final, array $items, array $discounts, array $taxes)
    {
        $this->mode      = $mode;
        $this->gross     = $gross;
        $this->final     = $final;
        $this->items     = $items;
        $this->discounts = $discounts;
        $this->taxes     = $taxes;
    }

    /**
     * @return Total
     */
    public function getGross()
    {
        return $this->gross;
    }

    /**
     * @return Total
     */
    public function getFinal()
    {
        return $this->final;
    }

    /**
     * Returns the lines.
     *
     * @return Line[]
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     * Returns the discounts lines.
     *
     * @return Line[]
     */
    public function getDiscounts()
    {
        return $this->discounts;
    }

    /**
     * Returns the taxes.
     *
     * @return Tax[]
     */
    public function getTaxes()
    {
        return $this->taxes;
    }
}
