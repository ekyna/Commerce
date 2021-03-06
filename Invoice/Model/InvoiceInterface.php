<?php

namespace Ekyna\Component\Commerce\Invoice\Model;

use DateTime;
use Ekyna\Component\Commerce\Common\Model as Common;
use Ekyna\Component\Commerce\Document\Model\DocumentInterface;
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
     * Returns the credit.
     *
     * @return bool
     */
    public function isCredit(): bool;

    /**
     * Sets the credit.
     *
     * @param bool $credit
     *
     * @return InvoiceInterface
     */
    public function setCredit(bool $credit): InvoiceInterface;

    /**
     * Sets the shipment.
     *
     * @param ShipmentInterface|null $shipment
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
     * @return DateTime
     */
    public function getDueDate(): ?DateTime;

    /**
     * Sets the due date.
     *
     * @param DateTime|null $dueDate
     *
     * @return $this|InvoiceInterface
     */
    public function setDueDate(DateTime $dueDate = null): InvoiceInterface;

    /**
     * Returns whether to ignore stock (credit only, won't impact sold quantities if true).
     *
     * @return bool
     */
    public function isIgnoreStock(): bool;

    /**
     * Sets whether to ignore stock (credit only, won't impact sold quantities if true).
     *
     * @param bool $ignoreStock
     *
     * @return InvoiceInterface
     */
    public function setIgnoreStock(bool $ignoreStock): InvoiceInterface;

    /**
     * Returns whether this invoice is paid.
     *
     * @return bool
     */
    public function isPaid(): bool;
}
