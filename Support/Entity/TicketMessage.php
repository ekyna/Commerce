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
        $last = $this->getTicket()->getMessages()->last();

        return $this === $last;
    }
}
