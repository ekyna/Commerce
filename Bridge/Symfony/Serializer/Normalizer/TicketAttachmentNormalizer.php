<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Bridge\Symfony\Serializer\Normalizer;

use Ekyna\Component\Commerce\Support\Model\TicketAttachmentInterface;

/**
 * Class TicketAttachmentNormalizer
 * @package Ekyna\Component\Commerce\Bridge\Symfony\Serializer\Normalizer
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class TicketAttachmentNormalizer extends AbstractAttachmentNormalizer
{
    /**
     * @inheritDoc
     *
     * @param TicketAttachmentInterface $object
     */
    public function normalize($object, string $format = null, array $context = [])
    {
        if (self::contextHasGroup(['Default', 'Ticket', 'TicketMessage', 'TicketAttachment'], $context)) {
            $data = $this->normalizeAttachment($object);

            $data['ticket'] = $object->getMessage()->getTicket()->getId();
            $data['message'] = $object->getMessage()->getId();

            return $data;
        }

        return parent::normalize($object, $format, $context);
    }
}
