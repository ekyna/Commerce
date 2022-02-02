<?php

namespace Ekyna\Component\Commerce\Supplier\Entity;

use Ekyna\Component\Commerce\Common\Entity\AbstractAddress;
use Ekyna\Component\Commerce\Supplier\Model\SupplierAddressInterface;
use Ekyna\Component\Commerce\Supplier\Model\SupplierInterface;
use Ekyna\Component\Resource\Model\ResourceTrait;

/**
 * Class SupplierAddress
 * @package Ekyna\Component\Commerce\Supplier\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SupplierAddress extends AbstractAddress implements SupplierAddressInterface
{
    use ResourceTrait;

    /**
     * @var SupplierInterface
     */
    protected $supplier;

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
