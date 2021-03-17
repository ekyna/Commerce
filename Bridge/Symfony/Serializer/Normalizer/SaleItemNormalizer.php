<?php

namespace Ekyna\Component\Commerce\Bridge\Symfony\Serializer\Normalizer;

use Ekyna\Component\Commerce\Common\Model\SaleItemInterface;
use Ekyna\Component\Commerce\Invoice\Calculator\InvoiceSubjectCalculatorInterface;
use Ekyna\Component\Commerce\Invoice\Model\InvoiceSubjectInterface;
use Ekyna\Component\Commerce\Shipment\Calculator\ShipmentSubjectCalculatorInterface;
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
     * @var ShipmentSubjectCalculatorInterface
     */
    protected $shipmentCalculator;

    /**
     * @var InvoiceSubjectCalculatorInterface
     */
    protected $invoiceCalculator;

    /**
     * @var SubjectHelperInterface
     */
    protected $subjectHelper;


    /**
     * Constructor.
     *
     * @param ShipmentSubjectCalculatorInterface $shipmentCalculator
     * @param InvoiceSubjectCalculatorInterface  $invoiceCalculator
     * @param SubjectHelperInterface             $subjectHelper
     */
    public function __construct(
        ShipmentSubjectCalculatorInterface $shipmentCalculator,
        InvoiceSubjectCalculatorInterface $invoiceCalculator,
        SubjectHelperInterface $subjectHelper
    ) {
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
        $data = [];

        if ($this->contextHasGroup('Summary', $context)) {
            $children = [];
            foreach ($item->getChildren() as $child) {
                $children[] = $this->normalize($child, $format, $context);
            }

            $sale = $item->getSale();
            $total = $item->getTotalQuantity();

            $invoiceData = ['invoiced' => 0, 'credited' => 0, 'invoice_class' => null,];
            if ($sale instanceof InvoiceSubjectInterface && !$sale->isSample()) {
                $invoiceData = [
                    'invoiced' => $this->invoiceCalculator->calculateInvoicedQuantity($item),
                    'credited' => $this->invoiceCalculator->calculateCreditedQuantity($item, null, false),
                ];

                $invoiceable = $this->invoiceCalculator->calculateInvoiceableQuantity($item);
                if (0 === bccomp(0, $invoiceable, 3)) {
                    $invoiceData['invoice_class'] = 'success';
                } elseif (0 === bccomp($total, $invoiceable, 3)) {
                    $invoiceData['invoice_class'] = 'danger';
                } else {
                    $invoiceData['invoice_class'] = 'warning';
                }
            }

            $shipmentData = [
                'shipped'            => null,
                'returned'           => null,
                'available'          => null,
                'in_stock'           => null,
                'shipment_class'     => null,
                'availability_class' => null,
            ];
            if ($sale instanceof ShipmentSubjectInterface) {
                $shipmentData = [
                    'shipped'            => $this->shipmentCalculator->calculateShippedQuantity($item),
                    'returned'           => $this->shipmentCalculator->calculateReturnedQuantity($item),
                    'available'          => $this->shipmentCalculator->calculateAvailableQuantity($item),
                    'in_stock'           => $this->getInStock($item),
                    'availability_class' => null,
                ];

                $shippable = $this->shipmentCalculator->calculateShippableQuantity($item);
                if (0 === bccomp(0, $shippable, 3)) {
                    $invoiceData['shipment_class'] = 'success';
                } elseif (0 === bccomp($total, $shippable, 3)) {
                    $invoiceData['shipment_class'] = 'danger';
                } else {
                    $invoiceData['shipment_class'] = 'warning';
                }

                if (0 < $shippable) {
                    if (0 <= bccomp($shipmentData['available'], $shippable, 3)) {
                        $invoiceData['availability_class'] = 'success';
                    } elseif (0 === bccomp($shipmentData['available'], 0, 3)) {
                        $invoiceData['availability_class'] = 'danger';
                    } else {
                        $invoiceData['availability_class'] = 'warning';
                    }
                }
            }

            $data = array_replace($data, [
                'designation'    => $item->getDesignation(),
                'reference'      => $item->getReference(),
                'quantity'       => $item->getQuantity(),
                'total_quantity' => $total,
                'private'        => $item->isPrivate(),
                //'compound'         => $item->isCompound(),
                //'private_children' => $item->hasPrivateChildren(),
                'children'       => $children,
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
