<?php

namespace Ekyna\Component\Commerce\Invoice\Model;

use Ekyna\Component\Commerce\Common\Model as Common;
use Ekyna\Component\Commerce\Document\Model\DocumentInterface;
use Ekyna\Component\Commerce\Payment\Model\PaymentMethodInterface;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentInterface;
use Ekyna\Component\Resource\Model as Resource;

/**
 * Interface InvoiceInterface
 * @package Ekyna\Component\Commerce\Invoice\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 *
 * @method Common\SaleInterface|InvoiceSubjectInterface getSale()
 */
interface InvoiceInterface extends
    DocumentInterface,
    Resource\ResourceInterface,
    Resource\TimestampableInterface,
    Common\NumberSubjectInterface
{
    /**
     * Sets the shipment.
     *
     * @param ShipmentInterface $shipment
     *
     * @return $this|InvoiceInterface
     */
    public function setShipment(ShipmentInterface $shipment = null): InvoiceInterface;

    /**
     * Returns the shipment.
     *
     * @return ShipmentInterface
     */
    public function getShipment(): ?ShipmentInterface;

    /**
     * Returns the paid total.
     *
     * @return float
     */
    public function getPaidTotal(): float;

    /**
     * Sets the paid total.
     *
     * @param float $amount
     *
     * @return $this|InvoiceInterface
     */
    public function setPaidTotal(float $amount): InvoiceInterface;

    /**
     * Returns the real paid total.
     *
     * @return float
     */
    public function getRealPaidTotal(): float;

    /**
     * Sets the real paid total.
     *
     * @param float $amount
     *
     * @return $this|InvoiceInterface
     */
    public function setRealPaidTotal(float $amount): InvoiceInterface;

    /**
     * Returns the due date.
     *
     * @return \DateTime
     */
    public function getDueDate(): ?\DateTime;

    /**
     * Sets the due date.
     *
     * @param \DateTime $dueDate
     *
     * @return $this|InvoiceInterface
     */
    public function setDueDate(\DateTime $dueDate = null): InvoiceInterface;

    /**
     * Returns the payment method.
     *
     * @return PaymentMethodInterface
     */
    public function getPaymentMethod(): ?PaymentMethodInterface;

    /**
     * Sets the payment method.
     *
     * @param PaymentMethodInterface $method
     *
     * @return $this|InvoiceInterface
     */
    public function setPaymentMethod(PaymentMethodInterface $method = null): InvoiceInterface;
}
