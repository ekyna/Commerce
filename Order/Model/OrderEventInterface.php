<?php

namespace Ekyna\Component\Commerce\Order\Model;

/**
 * Interface OrderEventInterface
 * @package Ekyna\Component\Commerce\Order\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface OrderEventInterface
{
    /**
     * Returns the order.
     *
     * @return OrderInterface
     */
    public function getOrder();
}
