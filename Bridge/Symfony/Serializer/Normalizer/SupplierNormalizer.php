<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Bridge\Symfony\Serializer\Normalizer;

use Ekyna\Component\Commerce\Supplier\Model\SupplierInterface;
use Ekyna\Component\Resource\Bridge\Symfony\Serializer\ResourceNormalizer;

/**
 * Class SupplierNormalizer
 * @package Ekyna\Component\Commerce\Bridge\Symfony\Serializer\Normalizer
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SupplierNormalizer extends ResourceNormalizer
{
    /**
     * @inheritDoc
     *
     * @param SupplierInterface $object
     */
    public function normalize($object, string $format = null, array $context = [])
    {
        $data = parent::normalize($object, $format, $context);

        if (self::contextHasGroup('Search', $context)) {
            $data = array_replace($data, [
                'name'        => $object->getName(),
                'description' => $object->getDescription(),
            ]);
        } elseif (self::contextHasGroup('Summary', $context)) {
            $data = array_replace($data, [
                'description' => $object->getDescription(),
            ]);
        }

        return $data;
    }
}
