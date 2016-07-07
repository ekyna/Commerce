<?php

namespace Ekyna\Component\Commerce\Customer\Entity;

use Ekyna\Component\Commerce\Customer\Model\CustomerGroupInterface;

/**
 * Class Group
 * @package Ekyna\Component\Commerce\Customer\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CustomerGroup implements CustomerGroupInterface
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var boolean
     */
    protected $default;


    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->default = false;
    }

    /**
     * Returns the string representation.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->getName();
    }

    /**
     * Returns the id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @inheritdoc
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @inheritdoc
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getDefault()
    {
        return $this->default;
    }

    /**
     * @inheritdoc
     */
    public function setDefault($default)
    {
        $this->default = (bool)$default;
        return $this;
    }
}
