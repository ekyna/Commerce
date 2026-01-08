<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Bridge\Symfony\Serializer\Normalizer;

use Ekyna\Component\Commerce\Manufacture\Model\ProductionItemInterface;
use Ekyna\Component\Resource\Bridge\Symfony\Serializer\ResourceNormalizer;

use function array_replace;

/**
 * Class ProductionItemNormalizer
 * @package Ekyna\Component\Commerce\Bridge\Symfony\Serializer\Normalizer
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ProductionItemNormalizer extends ResourceNormalizer
{
    /**
     * @inheritDoc
     *
     * @param ProductionItemInterface $object
     */
    public function normalize($object, string $format = null, array $context = [])
    {
        $data = parent::normalize($object, $format, $context);

        if (self::contextHasGroup('Summary', $context)) {
            $order = $object->getProductionOrder();

            $data = array_replace($data, [
                'designation' => $object->getDesignation(),
                'reference'   => $object->getReference(),
                'quantity'    => $object->getQuantity()->toFixed(),
                'total'       => $object->getQuantity()->mul($order->getQuantity())->toFixed(),
            ]);
        }

        return $data;
    }
}
