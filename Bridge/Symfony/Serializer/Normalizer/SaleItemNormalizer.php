<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Bridge\Symfony\Serializer\Normalizer;

use Decimal\Decimal;
use Ekyna\Component\Commerce\Common\Model\SaleItemInterface;
use Ekyna\Component\Commerce\Common\Model\Units;
use Ekyna\Component\Commerce\Invoice\Calculator\InvoiceSubjectCalculatorInterface;
use Ekyna\Component\Commerce\Invoice\Model\InvoiceSubjectInterface;
use Ekyna\Component\Commerce\Shipment\Calculator\ShipmentSubjectCalculatorInterface;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentSubjectInterface;
use Ekyna\Component\Commerce\Stock\Model\StockSubjectInterface;
use Ekyna\Component\Commerce\Stock\Model\StockSubjectModes;
use Ekyna\Component\Commerce\Subject\SubjectHelperInterface;
use Ekyna\Component\Resource\Bridge\Symfony\Serializer\ResourceNormalizer;

use const INF;

/**
 * Class SaleItemNormalizer
 * @package Ekyna\Component\Commerce\Bridge\Symfony\Serializer\Normalizer
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SaleItemNormalizer extends ResourceNormalizer
{
    protected ShipmentSubjectCalculatorInterface $shipmentCalculator;
    protected InvoiceSubjectCalculatorInterface  $invoiceCalculator;
    protected SubjectHelperInterface             $subjectHelper;

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
     * @inheritDoc
     *
     * @param SaleItemInterface $object
     */
    public function normalize($object, string $format = null, array $context = [])
    {
        $data = [];

        if ($this->contextHasGroup('Summary', $context)) {
            $children = [];
            foreach ($object->getChildren() as $child) {
                $children[] = $this->normalize($child, $format, $context);
            }

            $sale = $object->getRootSale();
            $total = $object->getTotalQuantity();
            $unit = $object->getUnit();

            $invoiceData = ['invoiced' => 0, 'credited' => 0, 'invoice_class' => null,];
            if ($sale instanceof InvoiceSubjectInterface && !$sale->isSample()) {
                $invoiceData = [
                    'invoiced' => $this->invoiceCalculator->calculateInvoicedQuantity($object),
                    'credited' => $this->invoiceCalculator->calculateCreditedQuantity($object, null, false),
                ];

                $invoiceable = $this->invoiceCalculator->calculateInvoiceableQuantity($object);
                if ($invoiceable->isZero()) {
                    $invoiceData['invoice_class'] = 'success';
                } elseif ($invoiceable->equals($total)) {
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
                    'shipped'            => $this->shipmentCalculator->calculateShippedQuantity($object),
                    'returned'           => $this->shipmentCalculator->calculateReturnedQuantity($object),
                    'available'          => $this->shipmentCalculator->calculateAvailableQuantity($object),
                    'in_stock'           => $this->getInStock($object),
                    'availability_class' => null,
                ];

                $shippable = $this->shipmentCalculator->calculateShippableQuantity($object);
                if ($shippable->isZero()) {
                    $invoiceData['shipment_class'] = 'success';
                } elseif ($shippable->equals($total)) {
                    $invoiceData['shipment_class'] = 'danger';
                } else {
                    $invoiceData['shipment_class'] = 'warning';
                }

                if (0 < $shippable) {
                    if ($shipmentData['available'] > $shippable) {
                        $invoiceData['availability_class'] = 'success';
                    } elseif ($shipmentData['available']->isZero()) {
                        $invoiceData['availability_class'] = 'danger';
                    } else {
                        $invoiceData['availability_class'] = 'warning';
                    }
                }
            }

            $data = array_replace($data, [
                'designation'    => $object->getDesignation(),
                'reference'      => $object->getReference(),
                'quantity'       => $object->getQuantity(),
                'total_quantity' => $total,
                'private'        => $object->isPrivate(),
                //'compound'         => $item->isCompound(),
                //'private_children' => $item->hasPrivateChildren(),
                'children'       => $children,
            ], $shipmentData, $invoiceData);
        }

        return $data;
    }

    /**
     * Returns the item subject's in stock quantity.
     */
    private function getInStock(SaleItemInterface $item): Decimal
    {
        if (null === $subject = $this->subjectHelper->resolve($item, false)) {
            return new Decimal(INF);
        }

        if (!$subject instanceof StockSubjectInterface) {
            return new Decimal(INF);
        }

        if ($subject->getStockMode() === StockSubjectModes::MODE_DISABLED) {
            return new Decimal(INF);
        }

        return $subject->getInStock();
    }
}
