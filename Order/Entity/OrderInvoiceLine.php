<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Order\Entity;

use Ekyna\Component\Commerce\Common\Model\AdjustmentInterface;
use Ekyna\Component\Commerce\Common\Model\SaleItemInterface;
use Ekyna\Component\Commerce\Exception\UnexpectedTypeException;
use Ekyna\Component\Commerce\Invoice\Entity\AbstractInvoiceLine;
use Ekyna\Component\Commerce\Invoice\Model\InvoiceInterface;
use Ekyna\Component\Commerce\Invoice\Model\InvoiceLineInterface;
use Ekyna\Component\Commerce\Order\Model;

/**
 * Class OrderInvoiceLine
 * @package Ekyna\Component\Commerce\Order\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class OrderInvoiceLine extends AbstractInvoiceLine implements Model\OrderInvoiceLineInterface
{
    protected ?Model\OrderItemInterface       $orderItem       = null;
    protected ?Model\OrderAdjustmentInterface $orderAdjustment = null;

    public function setInvoice(?InvoiceInterface $invoice): InvoiceLineInterface
    {
        if ($invoice && !$invoice instanceof Model\OrderInvoiceInterface) {
            throw new UnexpectedTypeException($invoice, Model\OrderInvoiceInterface::class);
        }

        return parent::setInvoice($invoice);
    }

    public function setOrderItem(?Model\OrderItemInterface $item): Model\OrderInvoiceLineInterface
    {
        $this->orderItem = $item;

        return $this;
    }

    public function getOrderItem(): ?Model\OrderItemInterface
    {
        return $this->orderItem;
    }

    public function setOrderAdjustment(?Model\OrderAdjustmentInterface $adjustment): Model\OrderInvoiceLineInterface
    {
        $this->orderAdjustment = $adjustment;

        return $this;
    }

    public function getOrderAdjustment(): ?Model\OrderAdjustmentInterface
    {
        return $this->orderAdjustment;
    }

    public function setSaleItem(?SaleItemInterface $item): InvoiceLineInterface
    {
        if ($item && !$item instanceof Model\OrderItemInterface) {
            throw new UnexpectedTypeException($item, Model\OrderItemInterface::class);
        }

        return $this->setOrderItem($item);
    }

    public function getSaleItem(): ?SaleItemInterface
    {
        return $this->getOrderItem();
    }

    public function setSaleAdjustment(?AdjustmentInterface $adjustment): InvoiceLineInterface
    {
        if ($adjustment && !$adjustment instanceof Model\OrderAdjustmentInterface) {
            throw new UnexpectedTypeException($adjustment, Model\OrderAdjustmentInterface::class);
        }

        return $this->setOrderAdjustment($adjustment);
    }

    public function getSaleAdjustment(): ?AdjustmentInterface
    {
        return $this->getOrderAdjustment();
    }
}
