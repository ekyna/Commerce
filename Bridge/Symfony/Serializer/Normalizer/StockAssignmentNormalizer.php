<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Bridge\Symfony\Serializer\Normalizer;

use Ekyna\Component\Commerce\Bridge\Symfony\Serializer\Group;
use Ekyna\Component\Commerce\Common\Util\FormatterAwareTrait;
use Ekyna\Component\Commerce\Stock\Model\AssignmentInterface;
use Ekyna\Component\Resource\Bridge\Symfony\Serializer\ResourceNormalizer;

/**
 * Class StockAssignmentNormalizer
 * @package Ekyna\Component\Commerce\Bridge\Symfony\Serializer\Normalizer
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class StockAssignmentNormalizer extends ResourceNormalizer
{
    use FormatterAwareTrait;

    /**
     * @inheritDoc
     *
     * @param AssignmentInterface $object
     */
    public function normalize($object, string $format = null, array $context = []): array
    {
        $data = [];

        if (self::contextHasGroup([Group::STOCK_UNIT, Group::STOCK_ASSIGNMENT], $context)) {
            $formatter = $this->getFormatter();

            $data = array_replace($data, [
                'sold'    => $formatter->number($object->getSoldQuantity()),
                'shipped' => $formatter->number($object->getShippedQuantity()),
                'locked'  => $formatter->number($object->getLockedQuantity()),
                'ready'   => $object->isFullyShipped() || $object->isFullyShippable(),
            ]);

            if (self::contextHasGroup(Group::STOCK_ASSIGNMENT, $context)) {
                $data['unit'] = $this->normalizeObject($object->getStockUnit(), $format, $context);
            }
        }

        return $data;
    }
}
