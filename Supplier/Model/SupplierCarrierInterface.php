<?php

namespace Ekyna\Component\Commerce\Supplier\Model;

use Ekyna\Component\Commerce\Pricing\Model\TaxInterface;
use Ekyna\Component\Resource\Model\ResourceInterface;

/**
 * Interface SupplierCarrierInterface
 * @package Ekyna\Component\Commerce\Supplier\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface SupplierCarrierInterface extends ResourceInterface
{
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
     * @return $this|SupplierCarrierInterface
     */
    public function setName($name);

    /**
     * Returns the tax.
     *
     * @return TaxInterface
     */
    public function getTax();

    /**
     * Sets the tax.
     *
     * @param TaxInterface $tax
     *
     * @return $this|SupplierCarrierInterface
     */
    public function setTax(TaxInterface $tax = null);
}
