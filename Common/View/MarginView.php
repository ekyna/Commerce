<?php

namespace Ekyna\Component\Commerce\Common\View;

/**
 * Class MarginView
 * @package Ekyna\Component\Commerce\Common\View
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class MarginView
{
    /**
     * @var string
     */
    private $amount;

    /**
     * @var string
     */
    private $percent;


    /**
     * Constructor.
     *
     * @param string $amount
     * @param string $percent
     */
    public function __construct(string $amount, string $percent)
    {
        $this->amount = $amount;
        $this->percent = $percent;
    }

    /**
     * Returns the amount.
     *
     * @return string
     */
    public function getAmount(): string
    {
        return $this->amount;
    }

    /**
     * Returns the percent.
     *
     * @return string
     */
    public function getPercent(): string
    {
        return $this->percent;
    }
}
