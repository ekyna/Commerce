<?php

namespace Ekyna\Component\Commerce\Common\View;

/**
 * Class TotalView
 * @package Ekyna\Component\Commerce\Common\View
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class TotalView extends AbstractView
{
    /**
     * @var float
     */
    private $base;

    /**
     * @var float
     */
    private $tax;

    /**
     * @var float
     */
    private $total;


    /**
     * Constructor.
     *
     * @param float $base
     * @param float $tax
     * @param float $total
     */
    public function __construct($base, $tax, $total)
    {
        $this->base = $base;
        $this->tax = $tax;
        $this->total = $total;
    }

    /**
     * Returns the base total.
     *
     * @return float
     */
    public function getBase()
    {
        return $this->base;
    }

    /**
     * Returns the tax total.
     *
     * @return float
     */
    public function getTax()
    {
        return $this->tax;
    }

    /**
     * Returns the final total.
     *
     * @return float
     */
    public function getTotal()
    {
        return $this->total;
    }
}
