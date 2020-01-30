<?php

namespace Ekyna\Component\Commerce\Bridge\Symfony\Serializer\Normalizer;

use Ekyna\Component\Commerce\Common\Currency\CurrencyConverterInterface;
use Ekyna\Component\Commerce\Common\Util\FormatterAwareTrait;
use Ekyna\Component\Commerce\Common\Util\FormatterFactory;
use Ekyna\Component\Commerce\Stock\Model\StockUnitInterface;
use Ekyna\Component\Resource\Serializer\AbstractResourceNormalizer;

/**
 * Class StockUnitNormalizer
 * @package Ekyna\Component\Commerce\Bridge\Symfony\Serializer\Normalizer
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class StockUnitNormalizer extends AbstractResourceNormalizer
{
    use FormatterAwareTrait;

    /**
     * @var CurrencyConverterInterface
     */
    protected $currencyConverter;


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
     * @inheritdoc
     *
     * @param StockUnitInterface $unit
     */
    public function normalize($unit, $format = null, array $context = [])
    {
        $data = [];

        if ($this->contextHasGroup(['StockView', 'StockAssignment'], $context)) {
            $formatter = $this->getFormatter();

            if (null !== $eda = $unit->getEstimatedDateOfArrival()) {
                $eda = $formatter->date($eda);
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

            $default = $this->currencyConverter->getDefaultCurrency();
            $currency = $unit->getCurrency() ?? $default;
            $price = $formatter->currency($unit->getNetPrice(), $currency);
            if ($currency !== $default) {
                $real = $this
                    ->currencyConverter
                    ->convert($unit->getNetPrice(), $currency, $default, $unit->getExchangeDate());

                $price = sprintf('%s&nbsp;(%s)', $price, $formatter->currency($real, $default));
            }

            $data = array_replace($data, [
                'geocodes'    => implode(',', $unit->getGeocodes()),
                'ordered'     => $formatter->number($unit->getOrderedQuantity()),
                'received'    => $formatter->number($unit->getReceivedQuantity()),
                'adjusted'    => $formatter->number($unit->getAdjustedQuantity()),
                'sold'        => $formatter->number($unit->getSoldQuantity()),
                'shipped'     => $formatter->number($unit->getShippedQuantity()),
                'eda'         => $eda,
                'net_price'   => $price,
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
