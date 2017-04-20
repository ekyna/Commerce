<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Order\Model;

use Ekyna\Component\Commerce\Invoice\Model\InvoiceLineInterface;

/**
 * Interface OrderInvoiceLineInterface
 * @package Ekyna\Component\Commerce\Order\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface OrderInvoiceLineInterface extends InvoiceLineInterface
{
    public function setOrderItem(?OrderItemInterface $item): OrderInvoiceLineInterface;

    public function getOrderItem(): ?OrderItemInterface;

    public function setOrderAdjustment(?OrderAdjustmentInterface $adjustment): OrderInvoiceLineInterface;

    public function getOrderAdjustment(): ?OrderAdjustmentInterface;
}
