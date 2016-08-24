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
     * @var Line[]
     */
    private $lines;

    /**
     * @var float
     */
    private $base;

    /**
     * @var Tax[]
     */
    private $taxes;

    /**
     * @var float
     */
    private $total;

    /**
     * Constructor.
     *
     * @param string $mode
     * @param Line[] $lines
     * @param float  $base
     * @param Tax[]  $taxes
     * @param float  $total
     */
    public function __construct($mode, array $lines, $base, array $taxes, $total)
    {
        $this->mode = $mode;
        $this->lines = $lines;
        $this->base = $base;
        $this->taxes = $taxes;
        $this->total = $total;
    }

    /**
     * Returns the lines.
     *
     * @return Line[]
     */
    public function getLines()
    {
        return $this->lines;
    }

    /**
     * Returns the base.
     *
     * @return float
     */
    public function getBase()
    {
        return $this->base;
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

    /**
     * Returns the total.
     *
     * @return float
     */
    public function getTotal()
    {
        return $this->total;
    }
}
