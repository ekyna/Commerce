<?php

namespace Ekyna\Component\Commerce\Order\Entity;

use Ekyna\Component\Commerce\Common\Model\AdjustmentInterface;
use Ekyna\Component\Commerce\Common\Model\SaleItemInterface;
use Ekyna\Component\Commerce\Invoice\Entity\AbstractInvoiceLine;
use Ekyna\Component\Commerce\Invoice\Model\InvoiceInterface;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Order\Model;

/**
 * Class OrderInvoiceLine
 * @package Ekyna\Component\Commerce\Order\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class OrderInvoiceLine extends AbstractInvoiceLine implements Model\OrderInvoiceLineInterface
{
    /**
     * @var Model\OrderItemInterface
     */
    protected $orderItem;

    /**
     * @var Model\OrderAdjustmentInterface
     */
    protected $orderAdjustment;


    /**
     * @inheritDoc
     */
    public function setInvoice(InvoiceInterface $invoice = null)
    {
        if (null !== $invoice && !$invoice instanceof Model\OrderInvoiceInterface) {
            throw new InvalidArgumentException("Expected instance of OrderInvoiceInterface.");
        }

        return parent::setInvoice($invoice);
    }

    /**
     * @inheritDoc
     */
    public function setOrderItem(Model\OrderItemInterface $item = null)
    {
        $this->orderItem = $item;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getOrderItem()
    {
        return $this->orderItem;
    }

    /**
     * @inheritDoc
     */
    public function setOrderAdjustment(Model\OrderAdjustmentInterface $adjustment = null)
    {
        $this->orderAdjustment = $adjustment;

        return $this;
    }

    /**
     * @inheritDoc
     *
     * @return Model\OrderAdjustmentInterface
     */
    public function getOrderAdjustment()
    {
        return $this->orderAdjustment;
    }

    /**
     * @inheritDoc
     */
    public function setSaleItem(SaleItemInterface $item = null)
    {
        if (null !== $item && !$item instanceof Model\OrderItemInterface) {
            throw new InvalidArgumentException("Expected instance of OrderItemInterface.");
        }

        return $this->setOrderItem($item);
    }

    /**
     * @inheritDoc
     */
    public function getSaleItem()
    {
        return $this->getOrderItem();
    }

    /**
     * @inheritDoc
     */
    public function setSaleAdjustment(AdjustmentInterface $adjustment = null)
    {
        if (null !== $adjustment && !$adjustment instanceof Model\OrderAdjustmentInterface) {
            throw new InvalidArgumentException("Expected instance of OrderAdjustmentInterface.");
        }

        return $this->setOrderAdjustment($adjustment);
    }

    /**
     * @inheritDoc
     */
    public function getSaleAdjustment()
    {
        return $this->getOrderAdjustment();
    }
}
