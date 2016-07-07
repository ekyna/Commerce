<?php

namespace Ekyna\Component\Commerce\Order\Model;

use Ekyna\Component\Commerce\Address\Model\AddressInterface;

/**
 * Interface OrderAddressInterface
 * @package Ekyna\Component\Commerce\Order\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface OrderAddressInterface extends AddressInterface
{
    /**
     * Returns the id.
     *
     * @return int
     */
    public function getId();
}
