<?php

namespace Ekyna\Component\Commerce\Supplier\Entity;

use Ekyna\Component\Commerce\Common\Entity\AbstractAddress;
use Ekyna\Component\Commerce\Supplier\Model\SupplierAddressInterface;
use Ekyna\Component\Commerce\Supplier\Model\SupplierInterface;

/**
 * Class SupplierAddress
 * @package Ekyna\Component\Commerce\Supplier\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SupplierAddress extends AbstractAddress implements SupplierAddressInterface
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var SupplierInterface
     */
    protected $supplier;


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
    public function getSupplier()
    {
        return $this->supplier;
    }

    /**
     * @inheritdoc
     */
    public function setSupplier(SupplierInterface $supplier = null)
    {
        $this->supplier = $supplier;

        return $this;
    }
}
