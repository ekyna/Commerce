<?php

declare(strict_types=1);

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
     */
    public function getInvoiceOrder(): ?OrderInterface;

    /**
     * Sets the order this address is the invoice one.
     */
    public function setInvoiceOrder(?OrderInterface $order): OrderAddressInterface;

    /**
     * Returns the order this address is the delivery one.
     */
    public function getDeliveryOrder(): ?OrderInterface;

    /**
     * Sets the order this address is the delivery one.
     */
    public function setDeliveryOrder(?OrderInterface $order): OrderAddressInterface;

    public function getOrder(): ?OrderInterface;
}
