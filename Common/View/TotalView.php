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
    public function __construct(string $base, string $adjustment, string $total)
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
    public function getBase(): string
    {
        return $this->base;
    }

    /**
     * Returns the tax total.
     *
     * @return string
     */
    public function getAdjustment(): string
    {
        return $this->adjustment;
    }

    /**
     * Returns the final total.
     *
     * @return string
     */
    public function getTotal(): string
    {
        return $this->total;
    }
}
