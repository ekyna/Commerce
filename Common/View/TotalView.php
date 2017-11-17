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
     * @var string
     */
    private $base;

    /**
     * @var string
     */
    private $adjustment;

    /**
     * @var string
     */
    private $total;


    /**
     * Constructor.
     *
     * @param string $base
     * @param string $adjustment
     * @param string $total
     */
    public function __construct($base, $adjustment, $total)
    {
        $this->base = $base;
        $this->adjustment = $adjustment;
        $this->total = $total;
    }

    /**
     * Returns the base total.
     *
     * @return string
     */
    public function getBase()
    {
        return $this->base;
    }

    /**
     * Returns the tax total.
     *
     * @return string
     */
    public function getAdjustment()
    {
        return $this->adjustment;
    }

    /**
     * Returns the final total.
     *
     * @return string
     */
    public function getTotal()
    {
        return $this->total;
    }
}
