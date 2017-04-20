<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Support\Model;

use DateTimeInterface;
use Doctrine\Common\Collections\Collection;
use Ekyna\Component\Resource\Model\ResourceInterface;
use Ekyna\Component\Resource\Model\TimestampableInterface;

/**
 * Interface TicketMessageInterface
 * @package Ekyna\Component\Commerce\Support\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface TicketMessageInterface extends ResourceInterface, TimestampableInterface
{
    /**
     * Returns the ticket.
     */
    public function getTicket(): ?TicketInterface;

    /**
     * Sets the ticket.
     */
    public function setTicket(?TicketInterface $ticket): TicketMessageInterface;

    /**
     * Returns the author.
     */
    public function getAuthor(): ?string;

    /**
     * Sets the author.
     */
    public function setAuthor(?string $author): TicketMessageInterface;

    /**
     * Returns the content.
     */
    public function getContent(): ?string;

    /**
     * Sets the content.
     */
    public function setContent(?string $content): TicketMessageInterface;

    /**
     * Returns the 'notified at' date time.
     */
    public function getNotifiedAt(): ?DateTimeInterface;

    /**
     * Sets the 'notified at' date time.
     */
    public function setNotifiedAt(?DateTimeInterface $date): TicketMessageInterface;

    /**
     * @return Collection<TicketAttachmentInterface>
     */
    public function getAttachments(): Collection;

    public function addAttachment(TicketAttachmentInterface $attachment): TicketMessageInterface;

    public function removeAttachment(TicketAttachmentInterface $attachment): TicketMessageInterface;

    /**
     * Returns whether this message is internal.
     */
    public function isInternal(): bool;

    /**
     * Sets whether this message is internal.
     */
    public function setInternal(bool $internal): TicketMessageInterface;

    /**
     * Returns whether to notify the customer or admin.
     */
    public function isNotify(): bool;

    /**
     * Sets whether to notify the customer or admin.
     */
    public function setNotify(bool $notify): TicketMessageInterface;

    /**
     * Returns whether this message is from the customer.
     */
    public function isCustomer() : bool;

    /**
     * Returns whether this message is the latest.
     */
    public function isLatest(): bool;
}
