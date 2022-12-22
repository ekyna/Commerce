<?php

namespace Ekyna\Component\Commerce\Support\EventListener;

use Ekyna\Component\Commerce\Exception\UnexpectedTypeException;
use Ekyna\Component\Commerce\Support\Model\TicketAttachmentInterface;
use Ekyna\Component\Commerce\Support\Model\TicketMessageInterface;
use Ekyna\Component\Resource\Event\ResourceEventInterface;
use Ekyna\Component\Resource\Persistence\PersistenceHelperInterface;

/**
 * Class TicketAttachmentEventListener
 * @package Ekyna\Component\Commerce\Support\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class TicketAttachmentEventListener
{
    /**
     * @var PersistenceHelperInterface
     */
    protected $persistenceHelper;


    /**
     * Constructor.
     *
     * @param PersistenceHelperInterface $persistenceHelper
     */
    public function __construct(PersistenceHelperInterface $persistenceHelper)
    {
        $this->persistenceHelper = $persistenceHelper;
    }

    /**
     * Insert event handler.
     *
     * @param ResourceEventInterface $event
     */
    public function onInsert(ResourceEventInterface $event): void
    {
        $attachment = $this->getAttachmentFromEvent($event);

        $this->updateMessage($attachment->getMessage());
    }

    /**
     * Update event handler.
     *
     * @param ResourceEventInterface $event
     */
    public function onUpdate(ResourceEventInterface $event): void
    {
        $attachment = $this->getAttachmentFromEvent($event);

        $this->updateMessage($attachment->getMessage());
    }

    /**
     * Delete event handler.
     *
     * @param ResourceEventInterface $event
     */
    public function onDelete(ResourceEventInterface $event): void
    {
        $attachment = $this->getAttachmentFromEvent($event);

        if (null === $message = $attachment->getMessage()) {
            $message = $this->persistenceHelper->getChangeSet($attachment, 'message')[0];
        }

        $this->updateMessage($message);
    }

    /**
     * Updates the message.
     *
     * @param TicketMessageInterface $message
     */
    protected function updateMessage(TicketMessageInterface $message): void
    {
        $message->setUpdatedAt(new \DateTime());

        $this->persistenceHelper->persistAndRecompute($message, true);
    }

    /**
     * Returns the attachment from the event.
     *
     * @param ResourceEventInterface $event
     *
     * @return TicketAttachmentInterface
     */
    protected function getAttachmentFromEvent(ResourceEventInterface $event): TicketAttachmentInterface
    {
        $attachment = $event->getResource();

        if (!$attachment instanceof TicketAttachmentInterface) {
            throw new UnexpectedTypeException($attachment, TicketAttachmentInterface::class);
        }

        return $attachment;
    }
}
