<?php

namespace Ekyna\Component\Commerce\Document\Model;

use Ekyna\Component\Commerce\Common\Model as Common;

/**
 * Interface DocumentLineInterface
 * @package Ekyna\Component\Commerce\Document\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface DocumentLineInterface
{
    /**
     * Returns the document.
     *
     * @return DocumentInterface
     */
    public function getDocument();

    /**
     * Sets the document.
     *
     * @param DocumentInterface $document
     *
     * @return $this|DocumentLineInterface
     */
    public function setDocument(DocumentInterface $document = null);

    /**
     * Returns the sale.
     *
     * @return Common\SaleInterface
     */
    public function getSale();

    /**
     * Returns the sale item.
     *
     * @return Common\SaleItemInterface|null
     */
    public function getSaleItem();

    /**
     * Sets the sale item.
     *
     * @param Common\SaleItemInterface $item
     *
     * @return $this|DocumentLineInterface
     */
    public function setSaleItem(Common\SaleItemInterface $item = null);

    /**
     * Returns the sale adjustment.
     *
     * @return Common\AdjustmentInterface|null
     */
    public function getSaleAdjustment();

    /**
     * Sets the sale adjustment.
     *
     * @param Common\AdjustmentInterface $adjustment
     *
     * @return $this|DocumentLineInterface
     */
    public function setSaleAdjustment(Common\AdjustmentInterface $adjustment = null);

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
     * @return $this|DocumentLineInterface
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
     * @return $this|DocumentLineInterface
     */
    public function setDesignation($designation);

    /**
     * Returns the description.
     *
     * @return string
     */
    public function getDescription();

    /**
     * Sets the description.
     *
     * @param string $description
     *
     * @return $this|DocumentLineInterface
     */
    public function setDescription($description);

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
     * @return $this|DocumentLineInterface
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
     * @return $this|DocumentLineInterface
     */
    public function setNetPrice($price);

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
     * @return $this|DocumentLineInterface
     */
    public function setQuantity($quantity);

    /**
     * Returns the base total.
     *
     * @return float
     */
    public function getBaseTotal();

    /**
     * Sets the base total.
     *
     * @param float $baseTotal
     *
     * @return $this|DocumentLineInterface
     */
    public function setBaseTotal($baseTotal);

    /**
     * Returns the discount total.
     *
     * @return float
     */
    public function getDiscountTotal();

    /**
     * Sets the discount total.
     *
     * @param float $total
     *
     * @return $this|DocumentLineInterface
     */
    public function setDiscountTotal($total);

    /**
     * Returns the net total.
     *
     * @return float
     */
    public function getNetTotal();

    /**
     * Sets the net total.
     *
     * @param float $total
     *
     * @return $this|DocumentLineInterface
     */
    public function setNetTotal($total);

    /**
     * Returns the tax rates.
     *
     * @return array
     */
    public function getTaxRates();

    /**
     * Sets the tax rates.
     *
     * @param array $taxRates
     *
     * @return $this|DocumentLineInterface
     */
    public function setTaxRates(array $taxRates);
}
