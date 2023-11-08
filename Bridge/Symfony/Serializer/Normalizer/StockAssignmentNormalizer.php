<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Bridge\Symfony\Serializer\Normalizer;

use Ekyna\Component\Commerce\Common\Util\FormatterAwareTrait;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentStates;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentSubjectInterface;
use Ekyna\Component\Commerce\Stock\Model\StockAssignmentInterface;
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
     * @param StockAssignmentInterface $object
     */
    public function normalize($object, string $format = null, array $context = [])
    {
        $data = [];

        if (self::contextHasGroup(['StockView', 'StockAssignment'], $context)) {
            $formatter = $this->getFormatter();

            $data = array_replace($data, [
                'sold'    => $formatter->number($object->getSoldQuantity()),
                'shipped' => $formatter->number($object->getShippedQuantity()),
                'locked'  => $formatter->number($object->getLockedQuantity()),
                'ready'   => $object->isFullyShipped() || $object->isFullyShippable(),
            ]);

            if (self::contextHasGroup('StockView', $context)) {
                $sale = $object->getSaleItem()->getRootSale();
                $data['order_id'] = $sale->getId();
                $data['preparation'] =
                    $sale instanceof ShipmentSubjectInterface
                    && $sale->getShipmentState() === ShipmentStates::STATE_PREPARATION;
            }

            if (self::contextHasGroup('StockAssignment', $context)) {
                $data['unit'] = $this->normalizeObject($object->getStockUnit(), $format, $context);
            }
        }

        return $data;
    }
}
