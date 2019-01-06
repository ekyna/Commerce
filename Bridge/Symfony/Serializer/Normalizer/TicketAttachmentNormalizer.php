<?php

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
     * @param TicketAttachmentInterface $attachment
     */
    public function normalize($attachment, $format = null, array $context = [])
    {
        if ($this->contextHasGroup(['Default', 'Ticket', 'TicketMessage', 'TicketAttachment'], $context)) {
            $data = $this->normalizeAttachment($attachment);

            $data['message'] = $attachment->getMessage()->getId();

            return $data;
        }

        return parent::normalize($attachment, $format, $context);
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
        return $data instanceof TicketAttachmentInterface;
    }

    /**
     * @inheritDoc
     */
    public function supportsDenormalization($data, $type, $format = null)
    {
        return class_exists($type) && is_subclass_of($type, TicketAttachmentInterface::class);
    }
}