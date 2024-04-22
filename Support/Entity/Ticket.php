<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Support\Entity;

use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Ekyna\Component\Commerce\Common\Model\NumberSubjectTrait;
use Ekyna\Component\Commerce\Common\Model\StateSubjectTrait;
use Ekyna\Component\Commerce\Customer\Model\CustomerInterface;
use Ekyna\Component\Commerce\Order\Model\OrderInterface;
use Ekyna\Component\Commerce\Quote\Model\QuoteInterface;
use Ekyna\Component\Commerce\Support\Model\TicketInterface;
use Ekyna\Component\Commerce\Support\Model\TicketMessageInterface;
use Ekyna\Component\Commerce\Support\Model\TicketStates;
use Ekyna\Component\Commerce\Support\Model\TicketTagInterface;
use Ekyna\Component\Resource\Model\AbstractResource;
use Ekyna\Component\Resource\Model\TimestampableTrait;

/**
 * Class Ticket
 * @package Ekyna\Component\Commerce\Support\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class Ticket extends AbstractResource implements TicketInterface
{
    use NumberSubjectTrait;
    use StateSubjectTrait;
    use TimestampableTrait;

    protected ?string            $subject  = null;
    protected bool               $internal = false;
    protected ?DateTimeInterface $closedAt = null;
    protected ?CustomerInterface $customer = null;
    /** @var Collection<int, OrderInterface> */
    protected Collection $orders;
    /** @var Collection<int, QuoteInterface> */
    protected Collection $quotes;
    /** @var Collection<int, TicketMessageInterface> */
    protected Collection $messages;
    /** @var Collection<int, TicketTagInterface> */
    protected Collection $tags;


    public function __construct()
    {
        $this->state = TicketStates::STATE_OPENED;
        $this->internal = false;
        $this->orders = new ArrayCollection();
        $this->quotes = new ArrayCollection();
        $this->messages = new ArrayCollection();
        $this->tags = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->number ?: 'New ticket';
    }

    public function getSubject(): ?string
    {
        return $this->subject;
    }

    public function setSubject(?string $subject): TicketInterface
    {
        $this->subject = $subject;

        return $this;
    }

    public function getCustomer(): ?CustomerInterface
    {
        return $this->customer;
    }

    public function setCustomer(?CustomerInterface $customer = null): TicketInterface
    {
        $this->customer = $customer;

        return $this;
    }

    public function isInternal(): bool
    {
        return $this->internal;
    }

    public function setInternal(bool $internal): TicketInterface
    {
        $this->internal = $internal;

        return $this;
    }

    public function getClosedAt(): ?DateTimeInterface
    {
        return $this->closedAt;
    }

    public function setClosedAt(?DateTimeInterface $closedAt): TicketInterface
    {
        $this->closedAt = $closedAt;

        return $this;
    }

    public function getOrders(): Collection
    {
        return $this->orders;
    }

    public function addOrder(OrderInterface $order): TicketInterface
    {
        if (!$this->orders->contains($order)) {
            $this->orders->add($order);
        }

        return $this;
    }

    public function removeOrder(OrderInterface $order): TicketInterface
    {
        if ($this->orders->contains($order)) {
            $this->orders->removeElement($order);
        }

        return $this;
    }

    public function getQuotes(): Collection
    {
        return $this->quotes;
    }

    public function addQuote(QuoteInterface $quote): TicketInterface
    {
        if (!$this->quotes->contains($quote)) {
            $this->quotes->add($quote);
        }

        return $this;
    }

    public function removeQuote(QuoteInterface $quote): TicketInterface
    {
        if ($this->quotes->contains($quote)) {
            $this->quotes->removeElement($quote);
        }

        return $this;
    }

    public function getMessages(): Collection
    {
        return $this->messages;
    }

    public function addMessage(TicketMessageInterface $message): TicketInterface
    {
        if (!$this->messages->contains($message)) {
            $this->messages->add($message);
            $message->setTicket($this);
        }

        return $this;
    }

    public function removeMessage(TicketMessageInterface $message): TicketInterface
    {
        if ($this->messages->contains($message)) {
            $this->messages->removeElement($message);
            $message->setTicket(null);
        }

        return $this;
    }

    public function getTags(): Collection
    {
        return $this->tags;
    }

    public function addTag(TicketTagInterface $tag): TicketInterface
    {
        if (!$this->tags->contains($tag)) {
            $this->tags->add($tag);
        }

        return $this;
    }

    public function removeTag(TicketTagInterface $tag): TicketInterface
    {
        if ($this->tags->contains($tag)) {
            $this->tags->removeElement($tag);
        }

        return $this;
    }
}
