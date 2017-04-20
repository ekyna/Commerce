<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Invoice\Model;

use DateTimeInterface;
use Decimal\Decimal;
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
    Resource\RuntimeUidInterface,
    Common\NumberSubjectInterface
{
    /**
     * Returns whether this invoice is a credit.
     */
    public function isCredit(): bool;

    /**
     * Sets whether this invoice is a credit.
     */
    public function setCredit(bool $credit): InvoiceInterface;

    /**
     * Sets the shipment.
     */
    public function setShipment(?ShipmentInterface $shipment): InvoiceInterface;

    /**
     * Returns the shipment.
     */
    public function getShipment(): ?ShipmentInterface;

    public function getPaidTotal(): Decimal;

    public function setPaidTotal(Decimal $amount): InvoiceInterface;

    /**
     * Returns the real paid total (default currency).
     */
    public function getRealPaidTotal(): Decimal;

    /**
     * Sets the real paid total (default currency).
     */
    public function setRealPaidTotal(Decimal $amount): InvoiceInterface;

    public function getDueDate(): ?DateTimeInterface;

    /**
     * Sets the due date.
     */
    public function setDueDate(?DateTimeInterface $dueDate): InvoiceInterface;

    /**
     * Returns whether to ignore stock (credit only, won't impact sold quantities if true).
     */
    public function isIgnoreStock(): bool;

    /**
     * Sets whether to ignore stock (credit only, won't impact sold quantities if true).
     */
    public function setIgnoreStock(bool $ignoreStock): InvoiceInterface;

    /**
     * Returns whether this invoice is paid.
     */
    public function isPaid(): bool;
}
