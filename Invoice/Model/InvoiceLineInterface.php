<?php

namespace Ekyna\Component\Commerce\Invoice\Model;

use Ekyna\Component\Commerce\Common\Model\AdjustmentInterface;
use Ekyna\Component\Commerce\Common\Model\SaleItemInterface;
use Ekyna\Component\Resource\Model\ResourceInterface;

/**
 * Interface InvoiceLineInterface
 * @package Ekyna\Component\Commerce\Invoice\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface InvoiceLineInterface extends ResourceInterface
{
    /**
     * Returns the invoice.
     *
     * @return InvoiceInterface
     */
    public function getInvoice();

    /**
     * Sets the invoice.
     *
     * @param InvoiceInterface $invoice
     *
     * @return $this|InvoiceLineInterface
     */
    public function setInvoice(InvoiceInterface $invoice = null);

    /**
     * Returns the sale item.
     *
     * @return SaleItemInterface|null
     */
    public function getSaleItem();

    /**
     * Sets the sale item.
     *
     * @param SaleItemInterface $item
     *
     * @return $this|InvoiceLineInterface
     */
    public function setSaleItem(SaleItemInterface $item = null);

    /**
     * Returns the sale adjustment.
     *
     * @return AdjustmentInterface|null
     */
    public function getSaleAdjustment();

    /**
     * Sets the sale adjustment.
     *
     * @param AdjustmentInterface $adjustment
     *
     * @return $this|InvoiceLineInterface
     */
    public function setSaleAdjustment(AdjustmentInterface $adjustment = null);

    /**
     * Returns the type.
     *
     * @return string
     */
    public function getType();

    /**
     * Sets the type.
     *
     * @param string $type
     *
     * @return $this|InvoiceLineInterface
     */
    public function setType($type);

    /**
     * Returns the designation.
     *
     * @return string
     */
    public function getDesignation();

    /**
     * Sets the designation.
     *
     * @param string $designation
     *
     * @return $this|InvoiceLineInterface
     */
    public function setDesignation($designation);

    /**
     * Returns the reference.
     *
     * @return string
     */
    public function getReference();

    /**
     * Sets the reference.
     *
     * @param string $reference
     *
     * @return $this|InvoiceLineInterface
     */
    public function setReference($reference);

    /**
     * Returns the unit net price.
     *
     * @return float
     */
    public function getNetPrice();

    /**
     * Sets the unit net price.
     *
     * @param float $price
     *
     * @return $this|InvoiceLineInterface
     */
    public function setNetPrice($price);

    /**
     * Returns the taxes details.
     *
     * @return array
     */
    public function getTaxesDetails();

    /**
     * Sets the taxes details.
     *
     * @param array $details
     *
     * @return $this|InvoiceLineInterface
     */
    public function setTaxesDetails(array $details);

    /**
     * Returns the quantity.
     *
     * @return float
     */
    public function getQuantity();

    /**
     * Sets the quantity.
     *
     * @param float $quantity
     *
     * @return $this|InvoiceLineInterface
     */
    public function setQuantity($quantity);

    /**
     * Returns the base total.
     *
     * @return float
     */
    public function getBaseTotal();

    /**
     * Returns the tax total.
     *
     * @return float
     */
    public function getTaxesTotal();

    /**
     * Returns the total.
     *
     * @return float
     */
    public function getTotal();
}
