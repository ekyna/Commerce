<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Bridge\Symfony\Serializer\Normalizer;

use Ekyna\Component\Commerce\Common\Model\ProjectInterface;
use Ekyna\Component\Resource\Bridge\Symfony\Serializer\ResourceNormalizer;

/**
 * Class ProjectNormalizer
 * @package Ekyna\Component\Commerce\Bridge\Symfony\Serializer\Normalizer
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ProjectNormalizer extends ResourceNormalizer
{
    /**
     * @inheritDoc
     *
     * @param ProjectInterface $object
     */
    public function normalize($object, string $format = null, array $context = [])
    {
        $data = parent::normalize($object, $format, $context);

        if (self::contextHasGroup('Search', $context)) {
            $data = array_replace($data, [
                'name'        => $object->getName(),
                'description' => $object->getDescription(),
            ]);
        }

        return $data;
    }
}
