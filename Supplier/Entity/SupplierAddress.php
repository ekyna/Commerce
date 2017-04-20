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
     * @inheritDoc
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @inheritDoc
     */
    public function getSupplier()
    {
        return $this->supplier;
    }

    /**
     * @inheritDoc
     */
    public function setSupplier(SupplierInterface $supplier = null)
    {
        $this->supplier = $supplier;

        return $this;
    }
}
