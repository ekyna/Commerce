<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Bridge\Symfony\Serializer\Normalizer;

use Ekyna\Component\Commerce\Common\Util\FormatterAwareTrait;
use Ekyna\Component\Commerce\Support\Model\TicketAttachmentInterface;
use Ekyna\Component\Commerce\Support\Model\TicketMessageInterface;
use Ekyna\Component\Resource\Bridge\Symfony\Serializer\ResourceNormalizer;

/**
 * Class TicketMessageNormalizer
 * @package Ekyna\Component\Commerce\Bridge\Symfony\Serializer\Normalizer
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class TicketMessageNormalizer extends ResourceNormalizer
{
    use FormatterAwareTrait;


    /**
     * @inheritDoc
     *
     * @param TicketMessageInterface $object
     */
    public function normalize($object, string $format = null, array $context = [])
    {
        if ($this->contextHasGroup(['Default', 'Ticket'], $context)) {
            $formatter = $this->getFormatter();

            $data = [
                'id'            => $object->getId(),
                'ticket'        => $object->getTicket()->getId(),
                'content'       => $object->getContent(),
                'author'        => $object->getAuthor(),
                'internal'      => $object->isInternal(),
                'notify'        => $object->isNotify(),
                'notified_at'   => ($date = $object->getNotifiedAt()) ? $date->format('Y-m-d H:i:s') : null,
                'f_notified_at' => ($date = $object->getNotifiedAt()) ? $formatter->dateTime($date) : null,
                'created_at'    => ($date = $object->getCreatedAt()) ? $date->format('Y-m-d H:i:s') : null,
                'f_created_at'  => ($date = $object->getCreatedAt()) ? $formatter->dateTime($date) : null,
                'updated_at'    => ($date = $object->getUpdatedAt()) ? $date->format('Y-m-d H:i:s') : null,
                'f_updated_at'  => ($date = $object->getUpdatedAt()) ? $formatter->dateTime($date) : null,
                'attachments'   => [],
            ];

            foreach ($this->filterAttachments($object) as $attachment) {
                $data['attachments'][] = $this->normalizeObject($attachment, $format, $context);
            }

            return $data;
        }

        return parent::normalize($object, $format, $context);
    }

    /**
     * Filters the attachments.
     *
     * @param TicketMessageInterface $message
     *
     * @return TicketAttachmentInterface[]
     */
    protected function filterAttachments(TicketMessageInterface $message): array
    {
        return $message->getAttachments()->toArray();
    }
}
