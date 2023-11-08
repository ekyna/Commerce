<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Bridge\Symfony\Serializer\Normalizer;

use Ekyna\Component\Commerce\Common\Currency\CurrencyConverterInterface;
use Ekyna\Component\Commerce\Common\Util\FormatterAwareTrait;
use Ekyna\Component\Commerce\Common\Util\FormatterFactory;
use Ekyna\Component\Commerce\Stock\Model\StockUnitInterface;
use Ekyna\Component\Resource\Bridge\Symfony\Serializer\ResourceNormalizer;

/**
 * Class StockUnitNormalizer
 * @package Ekyna\Component\Commerce\Bridge\Symfony\Serializer\Normalizer
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class StockUnitNormalizer extends ResourceNormalizer
{
    use FormatterAwareTrait;

    protected CurrencyConverterInterface $currencyConverter;


    /**
     * Constructor.
     *
     * @param FormatterFactory           $formatterFactory
     * @param CurrencyConverterInterface $currencyConverter
     */
    public function __construct(
        FormatterFactory $formatterFactory,
        CurrencyConverterInterface $currencyConverter
    ) {
        $this->formatterFactory = $formatterFactory;
        $this->currencyConverter = $currencyConverter;
    }

    /**
     * @inheritDoc
     *
     * @param StockUnitInterface $object
     */
    public function normalize($object, string $format = null, array $context = [])
    {
        $data = [];

        if (self::contextHasGroup(['StockUnit', 'StockAssignment'], $context)) {
            $formatter = $this->getFormatter();

            if (null !== $eda = $object->getEstimatedDateOfArrival()) {
                $eda = $formatter->date($eda);
            }

            $adjustments = [];
            $assignments = [];

            if (self::contextHasGroup('StockView', $context)) {
                foreach ($object->getStockAdjustments() as $adjustment) {
                    $adjustments[] = $this->normalizeObject($adjustment, $format, $context);
                }
                foreach ($object->getStockAssignments() as $assignment) {
                    $assignments[] = $this->normalizeObject($assignment, $format, $context);
                }
            }

            $default = $this->currencyConverter->getDefaultCurrency();
            $price = sprintf(
                '%s&nbsp;<em>(%s)</em>',
                $formatter->currency($object->getNetPrice() + $object->getShippingPrice(), $default),
                $formatter->currency($object->getNetPrice(), $default)
            );

            $data = array_replace($data, [
                'geocodes'    => implode(',', $object->getGeocodes()),
                'ordered'     => $formatter->number($object->getOrderedQuantity()),
                'received'    => $formatter->number($object->getReceivedQuantity()),
                'adjusted'    => $formatter->number($object->getAdjustedQuantity()),
                'sold'        => $formatter->number($object->getSoldQuantity()),
                'shipped'     => $formatter->number($object->getShippedQuantity()),
                'locked'      => $formatter->number($object->getLockedQuantity()),
                'eda'         => $eda,
                'net_price'   => $price,
                'adjustments' => $adjustments,
                'assignments' => $assignments,
            ]);
        }

        return $data;
    }
}
