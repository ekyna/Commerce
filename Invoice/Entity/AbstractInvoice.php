<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Invoice\Entity;

use DateTime;
use DateTimeInterface;
use Decimal\Decimal;
use Ekyna\Component\Commerce\Common\Model\NumberSubjectTrait;
use Ekyna\Component\Commerce\Document\Model as Document;
use Ekyna\Component\Commerce\Document\Model\DocumentInterface;
use Ekyna\Component\Commerce\Invoice\Model as Invoice;
use Ekyna\Component\Resource\Model\ResourceTrait;
use Ekyna\Component\Resource\Model\RuntimeUidTrait;
use Ekyna\Component\Resource\Model\TimestampableTrait;

/**
 * Class AbstractInvoice
 * @package Ekyna\Component\Commerce\Invoice\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
abstract class AbstractInvoice extends Document\Document implements Invoice\InvoiceInterface
{
    use ResourceTrait;
    use NumberSubjectTrait;
    use TimestampableTrait;
    use RuntimeUidTrait;

    protected bool $credit;
    /** The paid total (document currency).*/
    protected Decimal $paidTotal;
    /** The paid total (default currency). */
    protected Decimal            $realPaidTotal;
    protected ?DateTimeInterface $dueDate = null;
    protected bool               $ignoreStock;


    public function __construct()
    {
        parent::__construct();

        $this->type = Document\DocumentTypes::TYPE_INVOICE;
        $this->credit = false;
        $this->paidTotal = new Decimal(0);
        $this->realPaidTotal = new Decimal(0);
        $this->ignoreStock = false;
        $this->createdAt = new DateTime();
    }

    public function __clone()
    {
        $this->id = null;
    }

    public function __toString(): string
    {
        return $this->number ?: 'New invoice';
    }

    public function isCredit(): bool
    {
        return $this->credit;
    }

    public function setCredit(bool $credit): Invoice\InvoiceInterface
    {
        $this->credit = $credit;

        $this->type = $credit ? Document\DocumentTypes::TYPE_CREDIT : Document\DocumentTypes::TYPE_INVOICE;

        return $this;
    }

    public function setType(string $type): DocumentInterface
    {
        $this->credit = $type === Document\DocumentTypes::TYPE_CREDIT;

        return parent::setType($type);
    }

    public function getPaidTotal(): Decimal
    {
        return $this->paidTotal;
    }

    public function setPaidTotal(Decimal $amount): Invoice\InvoiceInterface
    {
        $this->paidTotal = $amount;

        return $this;
    }

    public function getRealPaidTotal(): Decimal
    {
        return $this->realPaidTotal;
    }

    public function setRealPaidTotal(Decimal $amount): Invoice\InvoiceInterface
    {
        $this->realPaidTotal = $amount;

        return $this;
    }

    public function getDueDate(): ?DateTimeInterface
    {
        return $this->dueDate;
    }

    public function setDueDate(?DateTimeInterface $dueDate): Invoice\InvoiceInterface
    {
        $this->dueDate = $dueDate;

        return $this;
    }

    public function isIgnoreStock(): bool
    {
        return $this->ignoreStock;
    }

    public function setIgnoreStock(bool $ignoreStock): Invoice\InvoiceInterface
    {
        $this->ignoreStock = $ignoreStock;

        return $this;
    }

    public function isPaid(): bool
    {
        if (!$this->grandTotal->isZero() && !$this->paidTotal->isZero()) {
            return $this->grandTotal <= $this->paidTotal;
        }

        return false;
    }
}
