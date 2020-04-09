<?php

namespace Ekyna\Component\Commerce\Stock\Integrity;

/**
 * Class Action
 * @package Ekyna\Component\Commerce\Stock\Integrity
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class Action
{
    /** @var string */
    private $label;

    /**
     * @param string $label
     */
    public function __construct(string $label)
    {
        $this->label = $label;
    }

    /**
     * @return string
     */
    public function getLabel(): string
    {
        return $this->label;
    }
}
