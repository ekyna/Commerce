<?php

namespace Ekyna\Component\Commerce\Support\Model;

use Doctrine\Common\Collections\Collection;
use Ekyna\Component\Commerce\Common\Model\NumberSubjectInterface;
use Ekyna\Component\Commerce\Customer\Model\CustomerInterface;
use Ekyna\Component\Commerce\Order\Model\OrderInterface;
use Ekyna\Component\Commerce\Quote\Model\QuoteInterface;
use Ekyna\Component\Commerce\Support\Entity\TicketMessage;
use Ekyna\Component\Resource\Model\ResourceInterface;
use Ekyna\Component\Resource\Model\TimestampableInterface;

/**
 * Interface TicketInterface
 * @package Ekyna\Component\Commerce\Support\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface TicketInterface extends ResourceInterface, NumberSubjectInterface, TimestampableInterface
{
    /**
     * Returns the subject.
     *
     * @return string
     */
    public function getSubject();

    /**
     * Sets the subject.
     *
     * @param string $subject
     *
     * @return $this|TicketInterface
     */
    public function setSubject(string $subject);

    /**
     * Returns the state.
     *
     * @return string
     */
    public function getState();

    /**
     * Sets the state.
     *
     * @param string $state
     *
     * @return $this|TicketInterface
     */
    public function setState(string $state);

    /**
     * Returns whether this ticket is internal.
     *
     * @return bool
     */
    public function isInternal(): bool;

    /**
     * Sets whether this ticket is internal.
     *
     * @param bool $internal
     *
     * @return TicketInterface
     */
    public function setInternal(bool $internal): TicketInterface;

    /**
     * Returns the customer.
     *
     * @return CustomerInterface
     */
    public function getCustomer();

    /**
     * Sets the customer.
     *
     * @param CustomerInterface $customer
     *
     * @return $this|TicketInterface
     */
    public function setCustomer(CustomerInterface $customer = null);

    /**
     * Returns the order.
     *
     * @return Collection|OrderInterface[]
     */
    public function getOrders();

    /**
     * Adds the order.
     *
     * @param OrderInterface $order
     *
     * @return $this|TicketInterface
     */
    public function addOrder(OrderInterface $order);

    /**
     * Removes the order.
     *
     * @param OrderInterface $order
     *
     * @return $this|TicketInterface
     */
    public function removeOrder(OrderInterface $order);

    /**
     * Returns the quote.
     *
     * @return Collection|QuoteInterface[]
     */
    public function getQuotes();

    /**
     * Adds the quote.
     *
     * @param QuoteInterface $quote
     *
     * @return $this|TicketInterface
     */
    public function addQuote(QuoteInterface $quote);

    /**
     * Removes the quote.
     *
     * @param QuoteInterface $quote
     *
     * @return $this|TicketInterface
     */
    public function removeQuote(QuoteInterface $quote);

    /**
     * Returns the messages.
     *
     * @return Collection|TicketMessage[]
     */
    public function getMessages();

    /**
     * Adds the message.
     *
     * @param TicketMessage $message
     *
     * @return $this|TicketInterface
     */
    public function addMessage(TicketMessage $message);

    /**
     * Removes the message.
     *
     * @param TicketMessage $message
     *
     * @return $this|TicketInterface
     */
    public function removeMessage(TicketMessage $message);
}
