<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Customer\Balance;

use DateTimeInterface;
use Decimal\Decimal;

/**
 * Class Line
 * @package Ekyna\Component\Commerce\Customer\Balance
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class Line
{
    public const TYPE_FORWARD = 'forward';
    public const TYPE_INVOICE = 'invoice';
    public const TYPE_CREDIT  = 'credit';
    public const TYPE_PAYMENT = 'payment';
    public const TYPE_REFUND  = 'refund';

    private DateTimeInterface  $date;
    private string             $type;
    private string             $number;
    private Decimal            $debit;
    private Decimal            $credit;
    private int                $orderId;
    private string             $orderNumber;
    private string             $voucherNumber;
    private DateTimeInterface  $orderDate;
    private ?DateTimeInterface $dueDate;
    private bool               $due = false;

    public function __construct(
        DateTimeInterface $date,
        string            $type,
        string            $number,
        Decimal           $debit,
        Decimal           $credit,
        int               $orderId,
        string            $orderNumber,
        string            $voucherNumber,
        DateTimeInterface $orderDate,
        DateTimeInterface $dueDate = null
    ) {
        $this->date = $date;
        $this->type = $type;
        $this->number = $number;
        $this->debit = $debit;
        $this->credit = $credit;
        $this->orderId = $orderId;
        $this->orderNumber = $orderNumber;
        $this->voucherNumber = $voucherNumber;
        $this->orderDate = $orderDate;
        $this->dueDate = $dueDate;
    }

    public function getDate(): DateTimeInterface
    {
        return $this->date;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getNumber(): string
    {
        return $this->number;
    }

    public function addDebit(Decimal $debit): void
    {
        $this->debit += $debit;
    }

    public function getDebit(): Decimal
    {
        return $this->debit;
    }

    public function addCredit(Decimal $credit): void
    {
        $this->credit += $credit;
    }

    public function getCredit(): Decimal
    {
        return $this->credit;
    }

    public function getOrderId(): int
    {
        return $this->orderId;
    }

    public function getOrderNumber(): string
    {
        return $this->orderNumber;
    }

    public function getVoucherNumber(): string
    {
        return $this->voucherNumber;
    }

    public function getOrderDate(): DateTimeInterface
    {
        return $this->orderDate;
    }

    public function getDueDate(): ?DateTimeInterface
    {
        return $this->dueDate;
    }

    public function isDue(): bool
    {
        return $this->due;
    }

    public function setDue(bool $due): self
    {
        $this->due = $due;

        return $this;
    }
}
