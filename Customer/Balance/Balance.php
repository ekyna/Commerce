<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Customer\Balance;

use DateTimeInterface;
use Decimal\Decimal;
use Ekyna\Component\Commerce\Customer\Model\CustomerInterface;

/**
 * Class Balance
 * @package Ekyna\Component\Commerce\Customer\Balance
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class Balance
{
    public const FILTER_ALL             = 'all';
    public const FILTER_DUE_INVOICES    = 'due_invoices';
    public const FILTER_BEFALL_INVOICES = 'befall_invoices';

    private CustomerInterface  $customer;
    private string             $currency;
    private ?DateTimeInterface $from;
    private ?DateTimeInterface $to;
    private string             $filter;
    private bool               $public;
    /** @var Line[] */
    private array   $lines = [];
    private Decimal $creditForward;
    private Decimal $debitForward;
    private ?Decimal $credit = null;
    private ?Decimal $debit = null;

    public function __construct(
        CustomerInterface $customer,
        DateTimeInterface $from = null,
        DateTimeInterface $to = null,
        string            $filter = self::FILTER_ALL,
        bool              $public = true
    ) {
        $this->customer = $customer;
        $this->currency = $customer->getCurrency() ? $customer->getCurrency()->getCode() : null;
        $this->from = $from;
        $this->to = $to;
        $this->filter = $filter;
        $this->public = $public;

        $this->creditForward = new Decimal(0);
        $this->debitForward = new Decimal(0);
    }

    public function getCustomer(): CustomerInterface
    {
        return $this->customer;
    }

    public function getCurrency(): ?string
    {
        return $this->currency;
    }

    public function setCurrency(string $currency): Balance
    {
        $this->currency = $currency;

        return $this;
    }

    public function getFrom(): ?DateTimeInterface
    {
        return $this->from;
    }

    public function setFrom(?DateTimeInterface $from): self
    {
        if ($from) {
            $from->setTime(0, 0);
        }

        $this->from = $from;

        return $this;
    }

    public function getTo(): ?DateTimeInterface
    {
        return $this->to;
    }

    public function setTo(?DateTimeInterface $to): self
    {
        if ($to) {
            $to->setTime(23, 59, 59, 999999);
        }

        $this->to = $to;

        return $this;
    }

    public function getFilter(): string
    {
        return $this->filter;
    }

    public function setFilter(string $filter): self
    {
        $this->filter = $filter;

        return $this;
    }

    public function isPublic(): bool
    {
        return $this->public;
    }

    public function setPublic(bool $public): self
    {
        $this->public = $public;

        return $this;
    }

    /**
     * @return array<Line>
     */
    public function getLines(): array
    {
        return $this->lines;
    }

    public function addLine(Line $line): self
    {
        $this->lines[] = $line;

        return $this;
    }

    public function getCreditForward(): Decimal
    {
        return $this->creditForward;
    }

    public function addCreditForward(Decimal $amount): self
    {
        $this->creditForward += $amount;

        return $this;
    }

    public function getDebitForward(): Decimal
    {
        return $this->debitForward;
    }

    public function addDebitForward(Decimal $amount): self
    {
        $this->debitForward += $amount;

        return $this;
    }

    public function getCredit(): Decimal
    {
        if (null !== $this->credit) {
            return $this->credit;
        }

        $total = $this->creditForward;

        foreach ($this->lines as $line) {
            $total += $line->getCredit();
        }

        return $this->credit = $total;
    }

    public function getDebit(): Decimal
    {
        if (null !== $this->debit) {
            return $this->debit;
        }

        $total = $this->debitForward;

        foreach ($this->lines as $line) {
            $total += $line->getDebit();
        }

        return $this->debit = $total;
    }

    public function getDiff(): Decimal
    {
        return $this->getCredit() - $this->getDebit();
    }

    /**
     * Sorts the lines by date ascending.
     */
    public function sortLines(): self
    {
        usort($this->lines, function (Line $a, Line $b) {
            if ($a->getDate()->getTimestamp() === $b->getDate()->getTimestamp()) {
                return 0;
            }

            return $a->getDate()->getTimestamp() > $b->getDate()->getTimestamp() ? 1 : -1;
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
