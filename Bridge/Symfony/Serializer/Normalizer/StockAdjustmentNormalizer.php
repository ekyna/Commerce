<?php

namespace Ekyna\Component\Commerce\Bridge\Symfony\Serializer\Normalizer;

use Ekyna\Component\Commerce\Common\Util\Formatter;
use Ekyna\Component\Commerce\Stock\Model\StockAdjustmentInterface;
use Ekyna\Component\Resource\Serializer\AbstractResourceNormalizer;

/**
 * Class StockAdjustmentNormalizer
 * @package Ekyna\Component\Commerce\Bridge\Symfony\Serializer\Normalizer
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class StockAdjustmentNormalizer extends AbstractResourceNormalizer
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
     * @param StockAdjustmentInterface $adjustment
     */
    public function normalize($adjustment, $format = null, array $context = [])
    {
        //$data = parent::normalize($adjustment, $format, $context);
        $data = [];

        $groups = isset($context['groups']) ? (array)$context['groups'] : [];

        if (in_array('StockView', $groups)) {
            $data = array_replace($data, [
                'quantity' => $this->formatter->number($adjustment->getQuantity()),
                'reason'   => $adjustment->getReason(),
                'note'     => $adjustment->getNote(),
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
        return $data instanceof StockAdjustmentInterface;
    }

    /**
     * @inheritdoc
     */
    public function supportsDenormalization($data, $type, $format = null)
    {
        return class_exists($type) && is_subclass_of($type, StockAdjustmentInterface::class);
    }
}