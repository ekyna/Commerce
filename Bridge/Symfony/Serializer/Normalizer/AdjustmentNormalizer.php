<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Bridge\Symfony\Serializer\Normalizer;

use Ekyna\Component\Commerce\Common\Model\AdjustmentInterface;
use Ekyna\Component\Resource\Bridge\Symfony\Serializer\ResourceNormalizer;

/**
 * Class AdjustmentNormalizer
 * @package Ekyna\Component\Commerce\Bridge\Symfony\Serializer\Normalizer
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class AdjustmentNormalizer extends ResourceNormalizer
{
    /**
     * @inheritDoc
     *
     * @param AdjustmentInterface $object
     */
    public function normalize($object, $format = null, array $context = []): array
    {
        return [
            'id'          => $object->getId(),
            'mode'        => $object->getMode(),
            'type'        => $object->getType(),
            'amount'      => $object->getAmount()->toFixed(2),
            'designation' => $object->getDesignation(),
        ];
    }
}
