<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Bridge\Symfony\Serializer\Normalizer;

use Ekyna\Component\Commerce\Customer\Model\CustomerGroupInterface;
use Ekyna\Component\Resource\Bridge\Symfony\Serializer\ResourceNormalizer;

/**
 * Class CustomerGroupNormalizer
 * @package Ekyna\Component\Commerce\Bridge\Symfony\Serializer\Normalizer
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CustomerGroupNormalizer extends ResourceNormalizer
{
    /**
     * @inheritDoc
     *
     * @param CustomerGroupInterface $object
     */
    public function normalize($object, string $format = null, array $context = [])
    {
        if ($format === 'csv' && self::contextHasGroup('TableExport', $context)) {
            return (string)$object;
        }

        return parent::normalize($object, $format, $context);
    }
}
