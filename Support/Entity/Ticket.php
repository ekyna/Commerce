<?php

namespace Ekyna\Component\Commerce\Support\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Ekyna\Component\Commerce\Common\Model\NumberSubjectTrait;
use Ekyna\Component\Commerce\Common\Model\StateSubjectTrait;
use Ekyna\Component\Commerce\Customer\Model\CustomerInterface;
use Ekyna\Component\Commerce\Order\Model\OrderInterface;
use Ekyna\Component\Commerce\Quote\Model\QuoteInterface;
use Ekyna\Component\Commerce\Support\Model\TicketInterface;
use Ekyna\Component\Commerce\Support\Model\TicketMessageInterface;
use Ekyna\Component\Commerce\Support\Model\TicketStates;
use Ekyna\Component\Resource\Model\TimestampableTrait;

/**
 * Class Ticket
 * @package Ekyna\Component\Commerce\Support\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class Ticket implements TicketInterface
{
    use StateSubjectTrait,
        TimestampableTrait,
        NumberSubjectTrait;

    /**
     * @var int
     */
    protected $id;

    /**
     * @var string
     */
    protected $subject;

    /**
     * @var bool
     */
    protected $internal;

    /**
     * @var CustomerInterface
     */
    protected $customer;

    /**
     * @var ArrayCollection|OrderInterface[]
     */
    protected $orders;

    /**
     * @var ArrayCollection|QuoteInterface[]
     */
    protected $quotes;

    /**
     * @var ArrayCollection|TicketMessageInterface[]
     */
    protected $messages;


    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->state = TicketStates::STATE_OPENED;
        $this->internal = false;
        $this->orders = new ArrayCollection();
        $this->quotes = new ArrayCollection();
        $this->messages = new ArrayCollection();
    }

    /**
     * Returns the string representation.
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->number ?: 'New ticket';
    }

    /**
     * @inheritdoc
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @inheritdoc
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * @inheritdoc
     */
    public function setSubject(string $subject)
    {
        $this->subject = $subject;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getCustomer()
    {
        return $this->customer;
    }

    /**
     * @inheritDoc
     */
    public function isInternal(): bool
    {
        return $this->internal;
    }

    /**
     * @inheritDoc
     */
    public function setInternal(bool $internal): TicketInterface
    {
        $this->internal = $internal;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setCustomer(CustomerInterface $customer = null)
    {
        $this->customer = $customer;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getOrders()
    {
        return $this->orders;
    }

    /**
     * @inheritdoc
     */
    public function addOrder(OrderInterface $order)
    {
        if (!$this->orders->contains($order)) {
            $this->orders->add($order);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function removeOrder(OrderInterface $order)
    {
        if ($this->orders->contains($order)) {
            $this->orders->removeElement($order);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getQuotes()
    {
        return $this->quotes;
    }

    /**
     * @inheritdoc
     */
    public function addQuote(QuoteInterface $quote)
    {
        if (!$this->quotes->contains($quote)) {
            $this->quotes->add($quote);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function removeQuote(QuoteInterface $quote)
    {
        if ($this->quotes->contains($quote)) {
            $this->quotes->removeElement($quote);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getMessages()
    {
        return $this->messages;
    }

    /**
     * @inheritdoc
     */
    public function addMessage(TicketMessageInterface $message)
    {
        if (!$this->messages->contains($message)) {
            $this->messages->add($message);
            $message->setTicket($this);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function removeMessage(TicketMessageInterface $message)
    {
        if ($this->messages->contains($message)) {
            $this->messages->removeElement($message);
            $message->setTicket(null);
        }

        return $this;
    }
}
