<?php

namespace Ekyna\Component\Commerce\Order\Entity;

use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Invoice\Entity\AbstractInvoiceItem;
use Ekyna\Component\Commerce\Invoice\Model as Invoice;
use Ekyna\Component\Commerce\Order\Model as Order;

/**
 * Class OrderInvoiceItem
 * @package Ekyna\Component\Commerce\Order\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class OrderInvoiceItem extends AbstractInvoiceItem implements Order\OrderInvoiceItemInterface
{
    /**
     * @inheritDoc
     */
    public function setInvoice(Invoice\InvoiceInterface $invoice = null): Invoice\InvoiceItemInterface
    {
        if (null !== $invoice && !$invoice instanceof Order\OrderInvoiceInterface) {
            throw new InvalidArgumentException("Expected instance of OrderInvoiceInterface.");
        }

        return parent::setInvoice($invoice);
    }
}
