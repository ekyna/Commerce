<?php

namespace Ekyna\Component\Commerce\Support\Model;

use Ekyna\Component\Commerce\Common\Model\AttachmentInterface;

/**
 * Class TicketAttachmentInterface
 * @package Ekyna\Component\Commerce\Support\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface TicketAttachmentInterface extends AttachmentInterface
{
    /**
     * Returns the message.
     *
     * @return TicketMessageInterface
     */
    public function getMessage();

    /**
     * Sets the message.
     *
     * @param TicketMessageInterface $message
     *
     * @return $this|TicketAttachmentInterface
     */
    public function setMessage(TicketMessageInterface $message = null);
}
