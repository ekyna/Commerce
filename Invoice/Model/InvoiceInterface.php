<?php

namespace Ekyna\Component\Commerce\Invoice\Model;

use Ekyna\Component\Commerce\Common\Model as Common;
use Ekyna\Component\Commerce\Document\Model\DocumentInterface;
use Ekyna\Component\Commerce\Invoice\Entity\AbstractInvoice;
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
    public function setShipment(ShipmentInterface $shipment = null);

    /**
     * Returns the shipment.
     *
     * @return ShipmentInterface
     */
    public function getShipment();

    /**
     * Returns the due date.
     *
     * @return \DateTime
     */
    public function getDueDate();

    /**
     * Sets the due date.
     *
     * @param \DateTime $dueDate
     *
     * @return AbstractInvoice
     */
    public function setDueDate(\DateTime $dueDate = null);

    /**
     * Sets the payment method.
     *
     * @param PaymentMethodInterface $method
     *
     * @return $this|InvoiceInterface
     */
    public function setPaymentMethod(PaymentMethodInterface $method = null);

    /**
     * Returns the payment method.
     *
     * @return PaymentMethodInterface
     */
    public function getPaymentMethod();
}
