<?php

namespace Ekyna\Component\Commerce\Bridge\Symfony\Serializer\Normalizer;

use Ekyna\Component\Commerce\Common\Util\Formatter;
use Ekyna\Component\Commerce\Stock\Model\StockAssignmentInterface;
use Ekyna\Component\Resource\Serializer\AbstractResourceNormalizer;

/**
 * Class StockAssignmentNormalizer
 * @package Ekyna\Component\Commerce\Bridge\Symfony\Serializer\Normalizer
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class StockAssignmentNormalizer extends AbstractResourceNormalizer
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
     * @param StockAssignmentInterface $assignment
     */
    public function normalize($assignment, $format = null, array $context = [])
    {
        $data = [];

        if ($this->contextHasGroup(['StockView', 'StockAssignment'], $context)) {
            $data = array_replace($data, [
                'sold'    => $this->formatter->number($assignment->getSoldQuantity()),
                'shipped' => $this->formatter->number($assignment->getShippedQuantity()),
            ]);

            if ($this->contextHasGroup('StockView', $context)) {
                $data['order_id'] = $assignment->getSaleItem()->getSale()->getId();
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