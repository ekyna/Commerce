<?php

namespace Ekyna\Component\Commerce\Invoice\Builder;

use Ekyna\Component\Commerce\Common\Factory\SaleFactoryInterface;
use Ekyna\Component\Commerce\Common\Model\AdjustmentInterface;
use Ekyna\Component\Commerce\Common\Model\AdjustmentTypes;
use Ekyna\Component\Commerce\Common\Model\SaleItemInterface;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Exception\LogicException;
use Ekyna\Component\Commerce\Invoice\Model\InvoiceLineTypes;
use Ekyna\Component\Commerce\Invoice\Model\InvoiceTypes;
use Ekyna\Component\Commerce\Invoice\Util\InvoiceUtil;
use Ekyna\Component\Commerce\Invoice\Model\InvoiceInterface;

/**
 * Class InvoiceBuilder
 * @package Ekyna\Component\Commerce\Invoice\Builder
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class InvoiceBuilder implements InvoiceBuilderInterface
{
    /**
     * @var SaleFactoryInterface
     */
    private $factory;


    /**
     * Constructor.
     *
     * @param SaleFactoryInterface $factory
     */
    public function __construct(SaleFactoryInterface $factory)
    {
        $this->factory = $factory;
    }

    /**
     * @inheritDoc
     */
    public function build(InvoiceInterface $invoice)
    {
        if (null === $sale = $invoice->getSale()) {
            throw new LogicException("Invoice's sale must be set at this point.");
        }

        // Goods lines
        $this->buildGoodsLines($invoice);

        // Discounts lines
        $this->buildDiscountsLines($invoice);

        // Shipment line
        $this->buildShipmentLine($invoice);
    }

    /**
     * Builds the invoice's goods lines.
     *
     * @param InvoiceInterface $invoice
     */
    protected function buildGoodsLines(InvoiceInterface $invoice)
    {
        foreach ($invoice->getSale()->getItems() as $item) {
            $this->buildGoodLine($invoice, $item);
        }
    }

    /**
     * Builds the invoice good line from the given sale item.
     *
     * @param SaleItemInterface $item
     * @param InvoiceInterface  $invoice
     */
    protected function buildGoodLine(InvoiceInterface $invoice, SaleItemInterface $item)
    {
        $description = null;
        if ($item->isCompound() && $item->hasChildren()) {
            foreach ($item->getChildren() as $child) {
                $this->buildGoodLine($invoice, $child);
            }

            return;
        }

        $line = $this->factory->createLineForInvoice($invoice);
        $line
            ->setType(InvoiceLineTypes::TYPE_GOOD)
            ->setSaleItem($item)
            ->setDesignation($item->getDesignation())
            ->setDescription($description)
            ->setReference($item->getReference());

        $invoice->addLine($line);

        if ($invoice->getType() === InvoiceTypes::TYPE_INVOICE) {
            $max = InvoiceUtil::calculateMaxInvoiceQuantity($line);
            $line->setQuantity($max);
        } elseif ($invoice->getType() === InvoiceTypes::TYPE_CREDIT) {
            $max = InvoiceUtil::calculateMaxCreditQuantity($line);
        } else {
            throw new InvalidArgumentException("Unexpected invoice type.");
        }

        if (0 >= $max) {
            $invoice->removeLine($line);
        }

        if (!$item->isCompound() && $item->hasChildren()) {
            foreach ($item->getChildren() as $child) {
                $this->buildGoodLine($invoice, $child);
            }
        }
    }

    /**
     * Builds the invoice's discounts lines.
     *
     * @param InvoiceInterface $invoice
     */
    protected function buildDiscountsLines(InvoiceInterface $invoice)
    {
        $sale = $invoice->getSale();

        if (!$sale->hasAdjustments(AdjustmentTypes::TYPE_DISCOUNT)) {
            return;
        }

        $adjustments = $sale->getAdjustments();
        foreach ($adjustments as $adjustment) {
            if ($adjustment->getType() === AdjustmentTypes::TYPE_DISCOUNT) {
                $this->buildDiscountLine($invoice, $adjustment);
            }
        }
    }

    /**
     * Builds the discount line from the given adjustment.
     *
     * @param InvoiceInterface    $invoice
     * @param AdjustmentInterface $adjustment
     */
    protected function buildDiscountLine(InvoiceInterface $invoice, AdjustmentInterface $adjustment)
    {
        if ($adjustment->getType() !== AdjustmentTypes::TYPE_DISCOUNT) {
            throw new InvalidArgumentException("Unexpected adjustment type.");
        }

        $line = $this->factory->createLineForInvoice($invoice);
        $line
            ->setType(InvoiceLineTypes::TYPE_DISCOUNT)
            ->setSaleAdjustment($adjustment)
            ->setDesignation($adjustment->getDesignation());

        $invoice->addLine($line);
    }

    /**
     * Builds the invoice's shipment line.
     *
     * @param InvoiceInterface $invoice
     */
    protected function buildShipmentLine(InvoiceInterface $invoice)
    {
        $sale = $invoice->getSale();

        if (0 >= $sale->getShipmentAmount()) {
            return;
        }

        $line = $this->factory->createLineForInvoice($invoice);
        $line
            ->setType(InvoiceLineTypes::TYPE_SHIPMENT)
            ->setDesignation($sale->getPreferredShipmentMethod()->getTitle());

        $invoice->addLine($line);
    }
}
