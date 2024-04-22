<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Support\Model;

use DateTimeInterface;
use Doctrine\Common\Collections\Collection;
use Ekyna\Component\Commerce\Common\Model\NumberSubjectInterface;
use Ekyna\Component\Commerce\Common\Model\StateSubjectInterface;
use Ekyna\Component\Commerce\Customer\Model\CustomerInterface;
use Ekyna\Component\Commerce\Order\Model\OrderInterface;
use Ekyna\Component\Commerce\Quote\Model\QuoteInterface;
use Ekyna\Component\Resource\Model\ResourceInterface;
use Ekyna\Component\Resource\Model\TimestampableInterface;

/**
 * Interface TicketInterface
 * @package Ekyna\Component\Commerce\Support\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface TicketInterface extends ResourceInterface, StateSubjectInterface, NumberSubjectInterface, TimestampableInterface
{
    public function getSubject(): ?string;

    public function setSubject(?string $subject): TicketInterface;

    /**
     * Returns whether this ticket is internal.
     */
    public function isInternal(): bool;

    /**
     * Sets whether this ticket is internal.
     */
    public function setInternal(bool $internal): TicketInterface;

    public function getClosedAt(): ?DateTimeInterface;

    public function setClosedAt(?DateTimeInterface $closedAt): TicketInterface;

    public function getCustomer(): ?CustomerInterface;

    public function setCustomer(?CustomerInterface $customer): TicketInterface;

    /**
     * @return Collection<int, OrderInterface>
     */
    public function getOrders(): Collection;

    public function addOrder(OrderInterface $order): TicketInterface;

    public function removeOrder(OrderInterface $order): TicketInterface;

    /**
     * @return Collection<int, QuoteInterface>
     */
    public function getQuotes(): Collection;

    public function addQuote(QuoteInterface $quote): TicketInterface;

    public function removeQuote(QuoteInterface $quote): TicketInterface;

    /**
     * @return Collection<int, TicketMessageInterface>
     */
    public function getMessages(): Collection;

    public function addMessage(TicketMessageInterface $message): TicketInterface;

    public function removeMessage(TicketMessageInterface $message): TicketInterface;

    /**
     * @return Collection<int, TicketTagInterface>
     */
    public function getTags(): Collection;

    public function addTag(TicketTagInterface $tag): TicketInterface;

    public function removeTag(TicketTagInterface $tag): TicketInterface;
}
