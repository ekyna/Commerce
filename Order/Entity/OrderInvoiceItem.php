<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Order\Entity;

use Ekyna\Component\Commerce\Exception\UnexpectedTypeException;
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
    public function setInvoice(?Invoice\InvoiceInterface $invoice): Invoice\InvoiceItemInterface
    {
        if ($invoice && !$invoice instanceof Order\OrderInvoiceInterface) {
            throw new UnexpectedTypeException($invoice, Order\OrderInvoiceInterface::class);
        }

        return parent::setInvoice($invoice);
    }
}
