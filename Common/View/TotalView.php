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
    private $tax;

    /**
     * @var string
     */
    private $total;


    /**
     * Constructor.
     *
     * @param string $base
     * @param string $tax
     * @param string $total
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
    public function getTax()
    {
        return $this->tax;
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
