<?php

namespace Ekyna\Component\Commerce\Support\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Ekyna\Component\Commerce\Support\Model\TicketAttachmentInterface;
use Ekyna\Component\Commerce\Support\Model\TicketInterface;
use Ekyna\Component\Commerce\Support\Model\TicketMessageInterface;
use Ekyna\Component\Resource\Model\TimestampableTrait;

/**
 * Class TicketMessage
 * @package Ekyna\Component\Commerce\Support\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class TicketMessage implements TicketMessageInterface
{
    use TimestampableTrait;

    /**
     * @var int
     */
    protected $id;

    /**
     * @var TicketInterface
     */
    protected $ticket;

    /**
     * @var bool
     */
    protected $customer;

    /**
     * @var string
     */
    protected $author;

    /**
     * @var string
     */
    protected $content;

    /**
     * @var bool
     */
    protected $internal;

    /**
     * @var bool
     */
    protected $notify;

    /**
     * @var \DateTime
     */
    protected $notifiedAt;

    /**
     * @var ArrayCollection|TicketAttachment[]
     */
    protected $attachments;


    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->internal = false;
        $this->notify = true;
        $this->attachments = new ArrayCollection();
    }

    /**
     * @inheritdoc
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @inheritdoc
     */
    public function getTicket()
    {
        return $this->ticket;
    }

    /**
     * @inheritdoc
     */
    public function setTicket(TicketInterface $ticket = null)
    {
        if ($ticket !== $this->ticket) {
            if ($previous = $this->ticket) {
                $this->ticket = null;
                $previous->removeMessage($this);
            }

            if ($this->ticket = $ticket) {
                $this->ticket->addMessage($this);
            }
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getAuthor()
    {
        return $this->author;
    }

    /**
     * @inheritdoc
     */
    public function setAuthor(string $author)
    {
        $this->author = $author;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @inheritdoc
     */
    public function setContent(string $content)
    {
        $this->content = $content;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function isInternal()
    {
        return $this->internal;
    }

    /**
     * @inheritDoc
     */
    public function setInternal(bool $internal)
    {
        $this->internal = $internal;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function isNotify()
    {
        return $this->notify;
    }

    /**
     * @inheritDoc
     */
    public function setNotify(bool $notify)
    {
        $this->notify = $notify;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getNotifiedAt()
    {
        return $this->notifiedAt;
    }

    /**
     * @inheritdoc
     */
    public function setNotifiedAt(\DateTime $date = null)
    {
        $this->notifiedAt = $date;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getAttachments()
    {
        return $this->attachments;
    }

    /**
     * @inheritdoc
     */
    public function addAttachment(TicketAttachmentInterface $attachment)
    {
        if (!$this->attachments->contains($attachment)) {
            $this->attachments->add($attachment);
            $attachment->setMessage($this);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function removeAttachment(TicketAttachmentInterface $attachment)
    {
        if ($this->attachments->contains($attachment)) {
            $this->attachments->removeElement($attachment);
            $attachment->setMessage(null);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function isCustomer()
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function isLatest()
    {
        return $this === $this->getTicket()->getMessages()->last();
    }
}
