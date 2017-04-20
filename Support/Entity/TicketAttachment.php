<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Support\Entity;

use Ekyna\Component\Commerce\Common\Entity\AbstractAttachment;
use Ekyna\Component\Commerce\Support\Model\TicketAttachmentInterface;
use Ekyna\Component\Commerce\Support\Model\TicketMessageInterface;

/**
 * Class TicketAttachment
 * @package Ekyna\Component\Commerce\Support\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class TicketAttachment extends AbstractAttachment implements TicketAttachmentInterface
{
    protected ?TicketMessageInterface $message = null;


    public function getMessage(): ?TicketMessageInterface
    {
        return $this->message;
    }

    public function setMessage(TicketMessageInterface $message = null): TicketAttachmentInterface
    {
        if ($message === $this->message) {
            return $this;
        }

        if ($previous = $this->message) {
            $this->message = null;
            $previous->removeAttachment($this);
        }

        if ($this->message = $message) {
            $this->message->addAttachment($this);
        }

        return $this;
    }
}
