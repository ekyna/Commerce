<?php

namespace Ekyna\Component\Commerce\Bridge\Symfony\Serializer\Normalizer;

use Ekyna\Component\Commerce\Common\Util\FormatterAwareTrait;
use Ekyna\Component\Commerce\Support\Model\TicketMessageInterface;
use Ekyna\Component\Resource\Serializer\AbstractResourceNormalizer;

/**
 * Class TicketMessageNormalizer
 * @package Ekyna\Component\Commerce\Bridge\Symfony\Serializer\Normalizer
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class TicketMessageNormalizer extends AbstractResourceNormalizer
{
    use FormatterAwareTrait;


    /**
     * @inheritDoc
     *
     * @param TicketMessageInterface $message
     */
    public function normalize($message, $format = null, array $context = [])
    {
        if ($this->contextHasGroup(['Default', 'Ticket'], $context)) {
            $data = [
                'id'            => $message->getId(),
                'ticket'        => $message->getTicket()->getId(),
                'content'       => $message->getContent(),
                'author'        => $message->getAuthor(),
                'notified_at'   => ($date = $message->getNotifiedAt()) ? $date->format('Y-m-d H:i:s') : null,
                'f_notified_at' => ($date = $message->getNotifiedAt()) ? $this->formatter->dateTime($date) : null,
                'created_at'    => ($date = $message->getCreatedAt()) ? $date->format('Y-m-d H:i:s') : null,
                'f_created_at'  => ($date = $message->getCreatedAt()) ? $this->formatter->dateTime($date) : null,
                'updated_at'    => ($date = $message->getUpdatedAt()) ? $date->format('Y-m-d H:i:s') : null,
                'f_updated_at'  => ($date = $message->getUpdatedAt()) ? $this->formatter->dateTime($date) : null,
                'attachments'   => [],
            ];

            foreach ($this->filterAttachments($message) as $attachment) {
                $data['attachments'][] = $this->normalizeObject($attachment, $format, $context);
            }

            return $data;
        }

        return parent::normalize($message, $format, $context);
    }

    /**
     * Filters the attachments.
     *
     * @param TicketMessageInterface $message
     *
     * @return \Ekyna\Component\Commerce\Support\Model\TicketAttachmentInterface[]
     */
    protected function filterAttachments(TicketMessageInterface $message)
    {
        return $message->getAttachments()->toArray();
    }

    /**
     * @inheritDoc
     */
    public function denormalize($data, $class, $format = null, array $context = [])
    {
        //$object = parent::denormalize($data, $class, $format, $context);

        throw new \Exception('Not yet implemented');
    }

    /**
     * @inheritDoc
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof TicketMessageInterface;
    }

    /**
     * @inheritDoc
     */
    public function supportsDenormalization($data, $type, $format = null)
    {
        return class_exists($type) && is_subclass_of($type, TicketMessageInterface::class);
    }
}
