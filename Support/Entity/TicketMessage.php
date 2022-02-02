<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Support\Entity;

use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Ekyna\Component\Commerce\Support\Model\TicketAttachmentInterface;
use Ekyna\Component\Commerce\Support\Model\TicketInterface;
use Ekyna\Component\Commerce\Support\Model\TicketMessageInterface;
use Ekyna\Component\Resource\Model\AbstractResource;
use Ekyna\Component\Resource\Model\TimestampableTrait;

/**
 * Class TicketMessage
 * @package Ekyna\Component\Commerce\Support\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class TicketMessage extends AbstractResource implements TicketMessageInterface
{
    use TimestampableTrait;

    protected ?TicketInterface   $ticket     = null;
    protected ?string            $author     = null;
    protected ?string            $content    = null;
    protected bool               $internal   = false;
    protected bool               $notify     = false;
    protected ?DateTimeInterface $notifiedAt = null;
    /** @var Collection<TicketAttachment> */
    protected Collection $attachments;

    public function __construct()
    {
        $this->internal = false;
        $this->notify = true;
        $this->attachments = new ArrayCollection();
    }

    public function getTicket(): ?TicketInterface
    {
        return $this->ticket;
    }

    public function setTicket(TicketInterface $ticket = null): TicketMessageInterface
    {
        if ($ticket === $this->ticket) {
            return $this;
        }

        if ($previous = $this->ticket) {
            $this->ticket = null;
            $previous->removeMessage($this);
        }

        if ($this->ticket = $ticket) {
            $this->ticket->addMessage($this);
        }

        return $this;
    }

    public function getAuthor(): ?string
    {
        return $this->author;
    }

    public function setAuthor(?string $author): TicketMessageInterface
    {
        $this->author = $author;

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(?string $content): TicketMessageInterface
    {
        $this->content = $content;

        return $this;
    }

    public function isInternal(): bool
    {
        return $this->internal;
    }

    public function setInternal(bool $internal): TicketMessageInterface
    {
        $this->internal = $internal;

        return $this;
    }

    public function isNotify(): bool
    {
        return $this->notify;
    }

    public function setNotify(bool $notify): TicketMessageInterface
    {
        $this->notify = $notify;

        return $this;
    }

    public function getNotifiedAt(): ?DateTimeInterface
    {
        return $this->notifiedAt;
    }

    public function setNotifiedAt(?DateTimeInterface $date): TicketMessageInterface
    {
        $this->notifiedAt = $date;

        return $this;
    }

    public function getAttachments(): Collection
    {
        return $this->attachments;
    }

    public function addAttachment(TicketAttachmentInterface $attachment): TicketMessageInterface
    {
        if (!$this->attachments->contains($attachment)) {
            $this->attachments->add($attachment);
            $attachment->setMessage($this);
        }

        return $this;
    }

    public function removeAttachment(TicketAttachmentInterface $attachment): TicketMessageInterface
    {
        if ($this->attachments->contains($attachment)) {
            $this->attachments->removeElement($attachment);
            $attachment->setMessage(null);
        }

        return $this;
    }

    public function isCustomer(): bool
    {
        return true;
    }

    public function isLatest(): bool
    {
        return $this === $this->getTicket()->getMessages()->last();
    }
}
