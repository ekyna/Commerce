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
}
