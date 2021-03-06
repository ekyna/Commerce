<?php

namespace Ekyna\Component\Commerce\Customer\Balance;

/**
 * Class Line
 * @package Ekyna\Component\Commerce\Customer\Balance
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class Line
{
    const TYPE_FORWARD = 'forward';
    const TYPE_INVOICE = 'invoice';
    const TYPE_CREDIT  = 'credit';
    const TYPE_PAYMENT = 'payment';
    const TYPE_REFUND  = 'refund';

    /**
     * @var \DateTime
     */
    private $date;

    /**
     * @var string;
     */
    private $type;

    /**
     * @var string
     */
    private $number;

    /**
     * @var float
     */
    private $debit;

    /**
     * @var float
     */
    private $credit;

    /**
     * @var int
     */
    private $orderId;

    /**
     * @var string
     */
    private $orderNumber;

    /**
     * @var string
     */
    private $voucherNumber;

    /**
     * @var \DateTime
     */
    private $orderDate;

    /**
     * @var \DateTime
     */
    private $dueDate;

    /**
     * @var bool;
     */
    private $due = false;


    /**
     * Constructor.
     *
     * @param \DateTime $date
     * @param string    $type
     * @param string    $number
     * @param float     $debit
     * @param float     $credit
     * @param int       $orderId
     * @param string    $orderNumber
     * @param string    $voucherNumber
     * @param \DateTime $orderDate
     * @param \DateTime $dueDate
     */
    public function __construct(
        \DateTime $date,
        string $type,
        string $number,
        float $debit,
        float $credit,
        int $orderId,
        string $orderNumber,
        string $voucherNumber,
        \DateTime $orderDate,
        \DateTime $dueDate = null
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

    /**
     * Returns the date.
     *
     * @return \DateTime
     */
    public function getDate(): \DateTime
    {
        return $this->date;
    }

    /**
     * Returns the identity.
     *
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * Returns the number.
     *
     * @return string
     */
    public function getNumber(): string
    {
        return $this->number;
    }

    /**
     * Adds the debit.
     *
     * @param float $debit
     */
    public function addDebit(float $debit): void
    {
        $this->debit += $debit;
    }

    /**
     * Returns the debit.
     *
     * @return float
     */
    public function getDebit(): float
    {
        return $this->debit;
    }

    /**
     * Adds the credit.
     *
     * @param float $credit
     */
    public function addCredit(float $credit): void
    {
        $this->credit += $credit;
    }

    /**
     * Returns the credit.
     *
     * @return float
     */
    public function getCredit(): float
    {
        return $this->credit;
    }

    /**
     * Returns the order id.
     *
     * @return int
     */
    public function getOrderId(): int
    {
        return $this->orderId;
    }

    /**
     * Returns the order number.
     *
     * @return string
     */
    public function getOrderNumber(): string
    {
        return $this->orderNumber;
    }

    /**
     * Returns the voucher number.
     *
     * @return string
     */
    public function getVoucherNumber(): string
    {
        return $this->voucherNumber;
    }

    /**
     * Returns the order date.
     *
     * @return \DateTime
     */
    public function getOrderDate(): \DateTime
    {
        return $this->orderDate;
    }

    /**
     * Returns the due date.
     *
     * @return \DateTime
     */
    public function getDueDate(): ?\DateTime
    {
        return $this->dueDate;
    }

    /**
     * Returns the done.
     *
     * @return bool
     */
    public function isDue(): bool
    {
        return $this->due;
    }

    /**
     * Sets the done.
     *
     * @param bool $due
     *
     * @return Line
     */
    public function setDue(bool $due): self
    {
        $this->due = $due;

        return $this;
    }
}
