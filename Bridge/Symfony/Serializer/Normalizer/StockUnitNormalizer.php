<?php

namespace Ekyna\Component\Commerce\Bridge\Symfony\Serializer\Normalizer;

use Ekyna\Component\Commerce\Common\Util\Formatter;
use Ekyna\Component\Commerce\Stock\Model\StockUnitInterface;
use Ekyna\Component\Resource\Serializer\AbstractResourceNormalizer;

/**
 * Class StockUnitNormalizer
 * @package Ekyna\Component\Commerce\Bridge\Symfony\Serializer\Normalizer
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class StockUnitNormalizer extends AbstractResourceNormalizer
{
    /**
     * @var Formatter
     */
    protected $formatter;


    /**
     * Constructor.
     *
     * @param Formatter $formatter
     */
    public function __construct(Formatter $formatter)
    {
        $this->formatter = $formatter;
    }

    /**
     * @inheritdoc
     *
     * @param StockUnitInterface $unit
     */
    public function normalize($unit, $format = null, array $context = [])
    {
        $data = [];

        if ($this->contextHasGroup(['StockView', 'StockAssignment'], $context)) {
            if (null !== $eda = $unit->getEstimatedDateOfArrival()) {
                $eda = $this->formatter->date($eda);
            }

            $adjustments = [];
            $assignments = [];

            if ($this->contextHasGroup('StockView', $context)) {
                foreach ($unit->getStockAdjustments() as $adjustment) {
                    $adjustments[] = $this->normalizeObject($adjustment, $format, $context);
                }
                foreach ($unit->getStockAssignments() as $assignment) {
                    $assignments[] = $this->normalizeObject($assignment, $format, $context);
                }
            }

            $data = array_replace($data, [
                'geocodes'    => implode(',', $unit->getGeocodes()),
                'ordered'     => $this->formatter->number($unit->getOrderedQuantity()),
                'received'    => $this->formatter->number($unit->getReceivedQuantity()),
                'adjusted'    => $this->formatter->number($unit->getAdjustedQuantity()),
                'sold'        => $this->formatter->number($unit->getSoldQuantity()),
                'shipped'     => $this->formatter->number($unit->getShippedQuantity()),
                'eda'         => $eda,
                'net_price'   => $this->formatter->currency($unit->getNetPrice()),
                'adjustments' => $adjustments,
                'assignments' => $assignments,
            ]);
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
        return $data instanceof StockUnitInterface;
    }

    /**
     * @inheritdoc
     */
    public function supportsDenormalization($data, $type, $format = null)
    {
        return class_exists($type) && is_subclass_of($type, StockUnitInterface::class);
    }
}