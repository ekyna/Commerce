<?php

namespace Ekyna\Component\Commerce\Order\Model;

use Ekyna\Component\Commerce\Common\Model\SaleAddressInterface;

/**
 * Interface OrderAddressInterface
 * @package Ekyna\Component\Commerce\Order\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface OrderAddressInterface extends SaleAddressInterface
{
    /**
     * Returns the order this address is the invoice one.
     *
     * @return OrderInterface|null
     */
    public function getInvoiceOrder();

    /**
     * Sets the order this address is the invoice one.
     *
     * @param OrderInterface $order
     *
     * @return $this|OrderAddressInterface
     */
    public function setInvoiceOrder(OrderInterface $order = null);

    /**
     * Returns the order this address is the delivery one.
     *
     * @return OrderInterface|null
     */
    public function getDeliveryOrder();

    /**
     * Sets the order this address is the delivery one.
     *
     * @param OrderInterface $order
     *
     * @return $this|OrderAddressInterface
     */
    public function setDeliveryOrder(OrderInterface $order = null);

    /**
     * Returns the related order.
     *
     * @return OrderInterface|null
     */
    public function getOrder();
}
