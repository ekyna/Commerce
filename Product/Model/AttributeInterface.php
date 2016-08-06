<?php

namespace Ekyna\Component\Commerce\Product\Model;

use Ekyna\Component\Commerce\Common\Model\EntityInterface;

/**
 * Interface AttributeInterface
 * @package Ekyna\Component\Commerce\Product\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface AttributeInterface extends EntityInterface
{
    /**
     * Returns the group.
     *
     * @return AttributeGroupInterface
     */
    public function getGroup();

    /**
     * Sets the group.
     *
     * @param AttributeGroupInterface $group
     *
     * @return $this|AttributeInterface
     */
    public function setGroup(AttributeGroupInterface $group);

    /**
     * Returns the name.
     *
     * @return string
     */
    public function getName();

    /**
     * Sets the name.
     *
     * @param string $name
     *
     * @return $this|AttributeInterface
     */
    public function setName($name);
}
