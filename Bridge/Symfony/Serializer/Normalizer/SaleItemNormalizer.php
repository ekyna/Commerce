<?php

namespace Ekyna\Component\Commerce\Bridge\Symfony\Serializer\Normalizer;

use Ekyna\Component\Commerce\Common\Model\SaleItemInterface;
use Ekyna\Component\Commerce\Common\Util\Formatter;
use Ekyna\Component\Commerce\Invoice\Calculator\InvoiceCalculatorInterface;
use Ekyna\Component\Commerce\Invoice\Model\InvoiceSubjectInterface;
use Ekyna\Component\Commerce\Shipment\Calculator\ShipmentCalculatorInterface;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentSubjectInterface;
use Ekyna\Component\Commerce\Stock\Model\StockSubjectInterface;
use Ekyna\Component\Commerce\Stock\Model\StockSubjectModes;
use Ekyna\Component\Commerce\Subject\SubjectHelperInterface;
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
     * @var SubjectHelperInterface
     */
    protected $subjectHelper;


    /**
     * Constructor.
     *
     * @param Formatter                   $formatter
     * @param ShipmentCalculatorInterface $shipmentCalculator
     * @param InvoiceCalculatorInterface  $invoiceCalculator
     * @param SubjectHelperInterface      $subjectHelper
     */
    public function __construct(
        Formatter $formatter,
        ShipmentCalculatorInterface $shipmentCalculator,
        InvoiceCalculatorInterface $invoiceCalculator,
        SubjectHelperInterface $subjectHelper
    ) {
        $this->formatter = $formatter;
        $this->shipmentCalculator = $shipmentCalculator;
        $this->invoiceCalculator = $invoiceCalculator;
        $this->subjectHelper = $subjectHelper;
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

            $sale = $item->getSale();

            $shipmentData = [
                'shipped'   => null,
                'returned'  => null,
                'available' => null,
                'in_stock'  => null,
            ];
            if ($sale instanceof ShipmentSubjectInterface) {
                if ($item->isCompound()) {
                    foreach ($children as $child) {
                        $quantity = $child['quantity'];
                        foreach (['shipped', 'returned', 'available', 'in_stock'] as $key) {
                            $qty = $child[$key] / $quantity;
                            if (null === $shipmentData[$key] || $shipmentData[$key] > $qty) {
                                $shipmentData[$key] = $qty;
                            }
                        }
                    }
                } else {
                    $shipmentData = [
                        'shipped'   => $this->shipmentCalculator->calculateShippedQuantity($item),
                        'returned'  => $this->shipmentCalculator->calculateReturnedQuantity($item),
                        'available' => $this->shipmentCalculator->calculateAvailableQuantity($item),
                        'in_stock'  => $this->getInStock($item),
                    ];
                }
            }

            $invoiceData = ['invoiced' => 0, 'credited' => 0];
            if ($sale instanceof InvoiceSubjectInterface) {
                $invoiceData = [
                    'invoiced' => $this->invoiceCalculator->calculateInvoicedQuantity($item),
                    'credited' => $this->invoiceCalculator->calculateCreditedQuantity($item),
                ];
            }

            $data = array_replace($data, [
                'designation'      => $item->getDesignation(),
                'reference'        => $item->getReference(),
                'quantity'         => $item->getQuantity(),
                'total_quantity'   => $item->getTotalQuantity(),
                'private'          => $item->isPrivate(),
                //'compound'         => $item->isCompound(),
                //'private_children' => $item->hasPrivateChildren(),
                'children'         => $children,
            ], $shipmentData, $invoiceData);
        }

        return $data;
    }

    /**
     * Returns the item subject's in stock quantity.
     *
     * @param SaleItemInterface $item
     *
     * @return float
     */
    private function getInStock(SaleItemInterface $item)
    {
        if (null === $subject = $this->subjectHelper->resolve($item, false)) {
            return INF;
        }

        if (!$subject instanceof StockSubjectInterface) {
            return INF;
        }

        if ($subject->isStockCompound()) {
            return INF;
        }

        if ($subject->getStockMode() === StockSubjectModes::MODE_DISABLED) {
            return INF;
        }

        return $subject->getInStock();
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