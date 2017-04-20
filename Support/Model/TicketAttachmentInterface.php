<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Support\Model;

use Ekyna\Component\Commerce\Common\Model\AttachmentInterface;

/**
 * Class TicketAttachmentInterface
 * @package Ekyna\Component\Commerce\Support\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface TicketAttachmentInterface extends AttachmentInterface
{
    public function getMessage(): ?TicketMessageInterface;

    public function setMessage(TicketMessageInterface $message = null): TicketAttachmentInterface;
}
