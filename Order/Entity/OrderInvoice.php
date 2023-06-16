<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Order\Entity;

use Ekyna\Component\Commerce\Common\Model\MarginSubjectTrait;
use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Document\Model\DocumentInterface;
use Ekyna\Component\Commerce\Exception\UnexpectedTypeException;
use Ekyna\Component\Commerce\Invoice\Entity\AbstractInvoice;
use Ekyna\Component\Commerce\Invoice\Model\InvoiceInterface;
use Ekyna\Component\Commerce\Order\Model;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentInterface;

/**
 * Class OrderInvoice
 * @package Ekyna\Component\Commerce\Order\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class OrderInvoice extends AbstractInvoice implements Model\OrderInvoiceInterface
{
    use MarginSubjectTrait;

    protected ?Model\OrderInterface         $order    = null;
    protected ?Model\OrderShipmentInterface $shipment = null;

    public function __construct()
    {
        parent::__construct();

        $this->initializeMargin();
    }

    public function getSale(): ?SaleInterface
    {
        return $this->getOrder();
    }

    public function setSale(?SaleInterface $sale): DocumentInterface
    {
        if ($sale && !$sale instanceof Model\OrderInterface) {
            throw new UnexpectedTypeException($sale, Model\OrderInterface::class);
        }

        return $this->setOrder($sale);
    }

    public function getOrder(): ?Model\OrderInterface
    {
        return $this->order;
    }

    public function setOrder(?Model\OrderInterface $order): Model\OrderInvoiceInterface
    {
        if ($order === $this->order) {
            return $this;
        }

        if ($previous = $this->order) {
            $this->order = null;
            $previous->removeInvoice($this);
        }

        if ($this->order = $order) {
            $this->order->addInvoice($this);
        }

        return $this;
    }

    /**
     * @return Model\OrderShipmentInterface|null
     */
    public function getShipment(): ?ShipmentInterface
    {
        return $this->shipment;
    }

    public function setShipment(ShipmentInterface $shipment = null): InvoiceInterface
    {
        if ($shipment && !$shipment instanceof Model\OrderShipmentInterface) {
            throw new UnexpectedTypeException($shipment, Model\OrderShipmentInterface::class);
        }

        if ($this->shipment === $shipment) {
            return $this;
        }

        if ($previous = $this->shipment) {
            $this->shipment = null;
            $previous->setInvoice(null);
        }

        if ($this->shipment = $shipment) {
            $this->shipment->setInvoice($this);
        }

        return $this;
    }
}
