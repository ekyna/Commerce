<?php

namespace Ekyna\Component\Commerce\Supplier\Model;

use Ekyna\Component\Commerce\Common\Model\AddressInterface;
use Ekyna\Component\Resource\Model\ResourceInterface;

/**
 * Interface SupplierAddressInterface
 * @package Ekyna\Component\Commerce\Supplier\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface SupplierAddressInterface extends ResourceInterface, AddressInterface
{
    /**
     * Returns the supplier.
     *
     * @return SupplierInterface
     */
    public function getSupplier();

    /**
     * Sets the supplier.
     *
     * @param SupplierInterface $supplier
     *
     * @return $this|SupplierAddressInterface
     */
    public function setSupplier(SupplierInterface $supplier = null);
}
