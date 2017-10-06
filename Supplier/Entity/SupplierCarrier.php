<?php

namespace Ekyna\Component\Commerce\Supplier\Entity;

use Ekyna\Component\Commerce\Supplier\Model\SupplierCarrierInterface;

/**
 * Class SupplierCarrier
 * @package Ekyna\Component\Commerce\Supplier\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SupplierCarrier implements SupplierCarrierInterface
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
     * Returns the string representation.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->name;
    }

    /**
     * @inheritdoc
     */
    public function getId()
    {
        return $this->id;
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
     * Sets the name.
     *
     * @param string $name
     *
     * @return SupplierCarrier
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }
}
