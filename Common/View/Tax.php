<?php

namespace Ekyna\Component\Commerce\Common\View;

/**
 * Class Tax
 * @package Ekyna\Component\Commerce\Common\View
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class Tax
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var float
     */
    private $total;


    /**
     * Constructor.
     *
     * @param string $name
     * @param float  $total
     */
    public function __construct($name, $total)
    {
        $this->name = $name;
        $this->total = $total;
    }

    /**
     * Returns the name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
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
