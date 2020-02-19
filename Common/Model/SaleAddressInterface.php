<?php

namespace Ekyna\Component\Commerce\Common\Model;

use Ekyna\Component\Resource\Model\ResourceInterface;

/**
 * Class SaleAddressInterface
 * @package Ekyna\Component\Commerce\Common\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface SaleAddressInterface extends AddressInterface, ResourceInterface
{
    /**
     * Returns the sale.
     *
     * @return SaleInterface
     */
    public function getSale();

    /**
     * Returns the information.
     *
     * @return string
     */
    public function getInformation(): ?string;

    /**
     * Sets the information.
     *
     * @param string $information
     *
     * @return $this|SaleAddressInterface
     */
    public function setInformation(string $information = null): SaleAddressInterface;
}
