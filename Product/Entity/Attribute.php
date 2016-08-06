<?php

namespace Ekyna\Component\Commerce\Product\Entity;

use Ekyna\Component\Commerce\Product\Model as Model;

/**
 * Class Attribute
 * @package Ekyna\Component\Commerce\Product\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class Attribute implements Model\AttributeInterface
{
    /**
     * @var integer
     */
    protected $id;

    /**
     * @var Model\AttributeGroupInterface
     */
    protected $group;

    /**
     * @var string
     */
    protected $name;


    /**
     * @inheritdoc
     */
    public function __toString()
    {
        return $this->getName();
    }

    /**
     * @inheritdoc
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @inheritdoc
     */
    public function getGroup()
    {
        return $this->group;
    }

    /**
     * @inheritdoc
     */
    public function setGroup(Model\AttributeGroupInterface $group)
    {
        $this->group = $group;
        $group->addAttribute($this);

        return $this;
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
}
