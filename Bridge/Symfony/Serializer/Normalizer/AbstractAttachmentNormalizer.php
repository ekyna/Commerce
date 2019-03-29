<?php

namespace Ekyna\Component\Commerce\Bridge\Symfony\Serializer\Normalizer;

use Ekyna\Component\Commerce\Common\Model\AttachmentInterface;
use Ekyna\Component\Commerce\Common\Util\FormatterAwareTrait;
use Ekyna\Component\Resource\Serializer\AbstractResourceNormalizer;

/**
 * Class AbstractAttachmentNormalizer
 * @package Ekyna\Component\Commerce\Bridge\Symfony\Serializer\Normalizer
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
abstract class AbstractAttachmentNormalizer extends AbstractResourceNormalizer
{
    use FormatterAwareTrait;


    /**
     * Normalizes the attachment.
     *
     * @param AttachmentInterface $attachment
     *
     * @return array
     */
    protected function normalizeAttachment(AttachmentInterface $attachment)
    {
        $formatter = $this->getFormatter();

        return [
            'id'           => $attachment->getId(),
            'title'        => $attachment->getTitle(),
            'type'         => $attachment->getType(),
            'size'         => $attachment->getSize(),
            'internal'     => $attachment->isInternal(),
            'file'         => pathinfo($attachment->getPath(), PATHINFO_BASENAME),
            'created_at'   => ($date = $attachment->getCreatedAt()) ? $date->format('Y-m-d H:i:s') : null,
            'f_created_at' => ($date = $attachment->getCreatedAt()) ? $formatter->dateTime($date) : null,
            'updated_at'   => ($date = $attachment->getUpdatedAt()) ? $date->format('Y-m-d H:i:s') : null,
            'f_updated_at' => ($date = $attachment->getUpdatedAt()) ? $formatter->dateTime($date) : null,
        ];
    }
}
