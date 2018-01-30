<?php

namespace Ekyna\Component\Commerce\Bridge\Symfony\Serializer\Normalizer;

use Ekyna\Component\Commerce\Common\Model\SaleItemInterface;
use Ekyna\Component\Commerce\Common\Util\Formatter;
use Ekyna\Component\Commerce\Invoice\Calculator\InvoiceCalculatorInterface;
use Ekyna\Component\Commerce\Shipment\Calculator\ShipmentCalculatorInterface;
use Ekyna\Component\Resource\Serializer\AbstractResourceNormalizer;

/**
 * Class SaleItemNormalizer
 * @package Ekyna\Component\Commerce\Bridge\Symfony\Serializer\Normalizer
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SaleItemNormalizer extends AbstractResourceNormalizer
{
    /**
     * @var Formatter
     */
    protected $formatter;

    /**
     * @var ShipmentCalculatorInterface
     */
    protected $shipmentCalculator;

    /**
     * @var InvoiceCalculatorInterface
     */
    protected $invoiceCalculator;


    /**
     * Constructor.
     *
     * @param Formatter                   $formatter
     * @param ShipmentCalculatorInterface $shipmentCalculator
     * @param InvoiceCalculatorInterface  $invoiceCalculator
     */
    public function __construct(
        Formatter $formatter,
        ShipmentCalculatorInterface $shipmentCalculator,
        InvoiceCalculatorInterface $invoiceCalculator
    ) {
        $this->formatter = $formatter;
        $this->shipmentCalculator = $shipmentCalculator;
        $this->invoiceCalculator = $invoiceCalculator;
    }

    /**
     * @inheritdoc
     *
     * @param SaleItemInterface $item
     */
    public function normalize($item, $format = null, array $context = [])
    {
        //$data = parent::normalize($item, $format, $context);
        $data = [];

        $groups = isset($context['groups']) ? (array)$context['groups'] : [];

        if (in_array('Summary', $groups)) {
            $children = [];
            foreach ($item->getChildren() as $child) {
                $children[] = $this->normalize($child, $format, $context);
            }

            $data = array_replace($data, [
                'designation' => $item->getDesignation(),
                'reference'   => $item->getReference(),
                'quantity'    => $item->getTotalQuantity(),
                'shipped'     => $this->shipmentCalculator->calculateShippedQuantity($item),
                'returned'    => $this->shipmentCalculator->calculateReturnedQuantity($item),
                'invoiced'    => $this->invoiceCalculator->calculateInvoicedQuantity($item),
                'credited'    => $this->invoiceCalculator->calculateCreditedQuantity($item),
                'private'     => $item->isPrivate(),
                'children'    => $children,
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
        return $data instanceof SaleItemInterface;
    }

    /**
     * @inheritdoc
     */
    public function supportsDenormalization($data, $type, $format = null)
    {
        return class_exists($type) && is_subclass_of($type, SaleItemInterface::class);
    }
}