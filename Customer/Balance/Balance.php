<?php

namespace Ekyna\Component\Commerce\Customer\Balance;

use Ekyna\Component\Commerce\Customer\Model\CustomerInterface;

/**
 * Class Balance
 * @package Ekyna\Component\Commerce\Customer\Balance
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class Balance
{
    /**
     * @var CustomerInterface
     */
    private $customer;

    /**
     * @var \DateTime
     */
    private $from;

    /**
     * @var \DateTime
     */
    private $to;

    /**
     * @var bool
     */
    private $byOrder;

    /**
     * @var bool
     */
    private $public;

    /**
     * @var Line[]
     */
    private $lines = [];

    /**
     * @var float
     */
    private $credit;

    /**
     * @var float
     */
    private $debit;

    /**
     * Constructor.
     *
     * @param CustomerInterface $customer
     * @param \DateTime         $from
     * @param \DateTime         $to
     * @param bool              $byOrder
     * @param bool              $public
     */
    public function __construct(
        CustomerInterface $customer,
        \DateTime $from = null,
        \DateTime $to = null,
        bool $byOrder = false,
        bool $public = true
    ) {
        $this->customer = $customer;
        $this->from = $from;
        $this->to = $to;
        $this->byOrder = $byOrder;
        $this->public = $public;
    }

    /**
     * Returns the customer.
     *
     * @return CustomerInterface
     */
    public function getCustomer(): CustomerInterface
    {
        return $this->customer;
    }

    /**
     * Returns the from.
     *
     * @return \DateTime
     */
    public function getFrom(): ?\DateTime
    {
        return $this->from;
    }

    /**
     * Sets the from.
     *
     * @param \DateTime $from
     *
     * @return Balance
     */
    public function setFrom(\DateTime $from = null): self
    {
        if ($from) {
            $from->setTime(0, 0, 0);
        }

        $this->from = $from;

        return $this;
    }

    /**
     * Returns the to.
     *
     * @return \DateTime
     */
    public function getTo(): ?\DateTime
    {
        return $this->to;
    }

    /**
     * Sets the to.
     *
     * @param \DateTime $to
     *
     * @return Balance
     */
    public function setTo(\DateTime $to = null): self
    {
        if ($to) {
            $to->setTime(23, 59, 59);
        }

        $this->to = $to;

        return $this;
    }

    /**
     * Returns the byOrder.
     *
     * @return bool
     */
    public function isByOrder(): bool
    {
        return $this->byOrder;
    }

    /**
     * Sets the byOrder.
     *
     * @param bool $byOrder
     *
     * @return Balance
     */
    public function setByOrder(bool $byOrder): self
    {
        $this->byOrder = $byOrder;

        return $this;
    }

    /**
     * Returns the public.
     *
     * @return bool
     */
    public function isPublic(): bool
    {
        return $this->public;
    }

    /**
     * Sets the public.
     *
     * @param bool $public
     *
     * @return Balance
     */
    public function setPublic(bool $public): self
    {
        $this->public = $public;

        return $this;
    }

    /**
     * Returns the lines.
     *
     * @return Line[]
     */
    public function getLines(): array
    {
        return $this->lines;
    }

    /**
     * Adds the line.
     *
     * @param Line $line
     *
     * @return Balance
     */
    public function addLine(Line $line): self
    {
        $this->lines[] = $line;

        return $this;
    }

    /**
     * Returns the credit.
     *
     * @return float
     */
    public function getCredit(): float
    {
        if (null !== $this->credit) {
            return $this->credit;
        }

        $total = 0;
        foreach ($this->lines as $line) {
            $total += $line->getCredit();
        }

        return $this->credit = $total;
    }

    /**
     * Returns the debit.
     *
     * @return float
     */
    public function getDebit(): float
    {
        if (null !== $this->debit) {
            return $this->debit;
        }

        $total = 0;
        foreach ($this->lines as $line) {
            $total += $line->getDebit();
        }

        return $this->debit = $total;
    }

    /**
     * Returns the diff.
     *
     * @return float
     */
    public function getDiff(): float
    {
        return $this->getCredit() - $this->getDebit();
    }

    /**
     * Sorts the lines by date ascending.
     *
     * @return Balance
     */
    public function sortLines(): self
    {
        if ($this->byOrder) {
            usort($this->lines, function (Line $a, Line $b) {
                if ($a->getOrderDate()->getTimestamp() === $b->getOrderDate()->getTimestamp()) {
                    if ($a->getDate()->getTimestamp() === $b->getDate()->getTimestamp()) {
                        return 0;
                    }

                    return $a->getDate()->getTimestamp() > $b->getDate()->getTimestamp()
                        ? 1 : -1;
                }

                return $a->getOrderDate()->getTimestamp() > $b->getOrderDate()->getTimestamp()
                    ? 1 : -1;
            });

            return $this;
        }

        usort($this->lines, function (Line $a, Line $b) {
            if ($a->getDate()->getTimestamp() === $b->getDate()->getTimestamp()) {
                return 0;
            }

            return $a->getDate()->getTimestamp() > $b->getDate()->getTimestamp()
                ? 1 : -1;
        });

        return $this;
    }

    /**
     * Clears debit and credit.
     */
    public function clear(): void
    {
        $this->debit = null;
        $this->credit = null;
    }
}
