<?php

namespace Ekyna\Component\Commerce\Common\View;

/**
 * Class TaxView
 * @package Ekyna\Component\Commerce\Common\View
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class TaxView extends AbstractView
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $total;


    /**
     * Constructor.
     *
     * @param string $name
     * @param string $total
     */
    public function __construct(string $name, string $total)
    {
        $this->name = $name;
        $this->total = $total;
    }

    /**
     * Returns the name.
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Returns the total.
     *
     * @return string
     */
    public function getTotal(): string
    {
        return $this->total;
    }
}
