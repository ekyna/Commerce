<?php

namespace Ekyna\Component\Commerce\Support\EventListener;

use Ekyna\Component\Commerce\Exception\UnexpectedValueException;
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
    public function onInsert(ResourceEventInterface $event)
    {
        $attachment = $this->getAttachmentFromEvent($event);

        $this->updateMessage($attachment->getMessage());
    }

    /**
     * Update event handler.
     *
     * @param ResourceEventInterface $event
     */
    public function onUpdate(ResourceEventInterface $event)
    {
        $attachment = $this->getAttachmentFromEvent($event);

        $this->updateMessage($attachment->getMessage());
    }

    /**
     * Delete event handler.
     *
     * @param ResourceEventInterface $event
     */
    public function onDelete(ResourceEventInterface $event)
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
    protected function updateMessage(TicketMessageInterface $message)
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
    protected function getAttachmentFromEvent(ResourceEventInterface $event)
    {
        $attachment = $event->getResource();

        if (!$attachment instanceof TicketAttachmentInterface) {
            throw new UnexpectedValueException("Expected instance of " . TicketAttachmentInterface::class);
        }

        return $attachment;
    }

}