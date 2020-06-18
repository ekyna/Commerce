<?php

namespace Ekyna\Component\Commerce\Bridge\Symfony\Serializer\Normalizer;

use Ekyna\Component\Commerce\Common\Util\FormatterAwareTrait;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentStates;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentSubjectInterface;
use Ekyna\Component\Commerce\Stock\Model\StockAssignmentInterface;
use Ekyna\Component\Resource\Serializer\AbstractResourceNormalizer;

/**
 * Class StockAssignmentNormalizer
 * @package Ekyna\Component\Commerce\Bridge\Symfony\Serializer\Normalizer
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class StockAssignmentNormalizer extends AbstractResourceNormalizer
{
    use FormatterAwareTrait;

    /**
     * @inheritdoc
     *
     * @param StockAssignmentInterface $assignment
     */
    public function normalize($assignment, $format = null, array $context = [])
    {
        $data = [];

        if ($this->contextHasGroup(['StockView', 'StockAssignment'], $context)) {
            $formatter = $this->getFormatter();

            $data = array_replace($data, [
                'sold'    => $formatter->number($assignment->getSoldQuantity()),
                'shipped' => $formatter->number($assignment->getShippedQuantity()),
                'locked'  => $formatter->number($assignment->getLockedQuantity()),
                'ready'   => $assignment->isFullyShipped() || $assignment->isFullyShippable(),
            ]);

            if ($this->contextHasGroup('StockView', $context)) {
                $sale                = $assignment->getSaleItem()->getSale();
                $data['order_id']    = $sale->getId();
                $data['preparation'] =
                    $sale instanceof ShipmentSubjectInterface
                    && $sale->getShipmentState() === ShipmentStates::STATE_PREPARATION;
            }

            if ($this->contextHasGroup('StockAssignment', $context)) {
                $data['unit'] = $this->normalizeObject($assignment->getStockUnit(), $format, $context);
            }
        }

        return $data;
    }

    /**
     * @inheritdoc
     */
    public function denormalize($data, $class, $format = null, array $context = [])
    {
        //$object = parent::denormalize($data, $class, $format, $context);

        throw new \Exception('Not yet implemented');
    }

    /**
     * @inheritdoc
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof StockAssignmentInterface;
    }

    /**
     * @inheritdoc
     */
    public function supportsDenormalization($data, $type, $format = null)
    {
        return class_exists($type) && is_subclass_of($type, StockAssignmentInterface::class);
    }
}
