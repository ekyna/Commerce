<?php

namespace Ekyna\Component\Commerce\Support\Model;

use Doctrine\Common\Collections\ArrayCollection;
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
     *
     * @return TicketInterface
     */
    public function getTicket();

    /**
     * Sets the ticket.
     *
     * @param TicketInterface $ticket
     *
     * @return $this|TicketMessageInterface
     */
    public function setTicket(TicketInterface $ticket = null);

    /**
     * Returns the author.
     *
     * @return string
     */
    public function getAuthor();

    /**
     * Sets the author.
     *
     * @param string $author
     *
     * @return $this|TicketMessageInterface
     */
    public function setAuthor(string $author);

    /**
     * Returns the content.
     *
     * @return string
     */
    public function getContent();

    /**
     * Sets the content.
     *
     * @param string $content
     *
     * @return $this|TicketMessageInterface
     */
    public function setContent(string $content);

    /**
     * Returns the 'notified at' date time.
     *
     * @return \DateTime
     */
    public function getNotifiedAt();

    /**
     * Sets the 'notified at' date time.
     *
     * @param \DateTime $date
     *
     * @return $this|TicketMessageInterface
     */
    public function setNotifiedAt(\DateTime $date = null);

    /**
     * Returns the attachments.
     *
     * @return ArrayCollection|TicketAttachmentInterface[]
     */
    public function getAttachments();

    /**
     * Adds the attachment.
     *
     * @param TicketAttachmentInterface $attachment
     *
     * @return $this|TicketMessageInterface
     */
    public function addAttachment(TicketAttachmentInterface $attachment);

    /**
     * Removes the attachment.
     *
     * @param TicketAttachmentInterface $attachment
     *
     * @return $this|TicketMessageInterface
     */
    public function removeAttachment(TicketAttachmentInterface $attachment);

    /**
     * Returns whether this message is internal.
     *
     * @return bool
     */
    public function isInternal();

    /**
     * Sets whether this message is internal.
     *
     * @param bool $internal
     *
     * @return TicketMessageInterface
     */
    public function setInternal(bool $internal);

    /**
     * Returns whether to notify the customer or admin.
     *
     * @return bool
     */
    public function isNotify();

    /**
     * Sets whether to notify the customer or admin.
     *
     * @param bool $notify
     *
     * @return TicketMessageInterface
     */
    public function setNotify(bool $notify);

    /**
     * Returns whether or not this message is from the customer.
     *
     * @return bool
     */
    public function isCustomer();

    /**
     * Returns whether or not this message is the latest.
     *
     * @return bool
     */
    public function isLatest();
}
