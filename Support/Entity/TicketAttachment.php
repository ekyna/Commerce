<?php

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
    /**
     * @var TicketMessageInterface
     */
    protected $message;


    /**
     * @inheritdoc
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @inheritdoc
     */
    public function setMessage(TicketMessageInterface $message = null)
    {
        if ($message !== $this->message) {
            if ($previous = $this->message) {
                $this->message = null;
                $previous->removeAttachment($this);
            }

            if ($this->message = $message) {
                $this->message->addAttachment($this);
            }
        }

        return $this;
    }
}
