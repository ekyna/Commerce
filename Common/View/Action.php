<?php

namespace Ekyna\Component\Commerce\Common\View;

/**
 * Class Action
 * @package Ekyna\Component\Commerce\Common\View
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class Action
{
    /**
     * @var string
     */
    private $path;

    /**
     * @var string
     */
    private $icon;

    /**
     * @var array
     */
    private $attributes;


    /**
     * Constructor.
     *
     * @param string $path
     * @param string $icon
     * @param array  $attributes
     */
    public function __construct($path, $icon, array $attributes)
    {
        $this->path = $path;
        $this->icon = $icon;
        $this->attributes = $attributes;
    }

    /**
     * Returns the path.
     *
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Returns the icon.
     *
     * @return string
     */
    public function getIcon()
    {
        return $this->icon;
    }

    /**
     * Returns the attributes.
     *
     * @return array
     */
    public function getAttributes()
    {
        return $this->attributes;
    }
}
