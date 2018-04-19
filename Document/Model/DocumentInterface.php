<?php

namespace Ekyna\Component\Commerce\Document\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Ekyna\Component\Commerce\Common\Model\SaleInterface;

/**
 * Interface DocumentInterface
 * @package Ekyna\Component\Commerce\Document\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface DocumentInterface
{
    /**
     * Returns the sale.
     *
     * @return SaleInterface
     */
    public function getSale();

    /**
     * Sets the sale.
     *
     * @param SaleInterface $sale
     *
     * @return $this|DocumentInterface
     */
    public function setSale(SaleInterface $sale = null);

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
     * @return $this|DocumentInterface
     */
    public function setType($type);

    /**
     * Returns the currency.
     *
     * @return string
     */
    public function getCurrency();

    /**
     * Sets the currency.
     *
     * @param string $currency
     *
     * @return $this|DocumentInterface
     */
    public function setCurrency($currency);

    /**
     * Returns the customer data.
     *
     * @return array
     */
    public function getCustomer();

    /**
     * Sets the customer data.
     *
     * @param array $data
     *
     * @return $this|DocumentInterface
     */
    public function setCustomer(array $data);

    /**
     * Returns the invoice address data.
     *
     * @return array
     */
    public function getInvoiceAddress();

    /**
     * Sets the invoice address data.
     *
     * @param array $data
     *
     * @return $this|DocumentInterface
     */
    public function setInvoiceAddress(array $data);

    /**
     * Returns the delivery address data.
     *
     * @return array
     */
    public function getDeliveryAddress();

    /**
     * Sets the delivery address data.
     *
     * @param array|null $data
     *
     * @return $this|DocumentInterface
     */
    public function setDeliveryAddress(array $data = null);

    /**
     * Returns the relay point data.
     *
     * @return array
     */
    public function getRelayPoint();

    /**
     * Sets the relay point data.
     *
     * @param array|null $data
     *
     * @return $this|DocumentInterface
     */
    public function setRelayPoint(array $data = null);

    /**
     * Returns whether the invoice has at least one line or not.
     *
     * @return bool
     */
    public function hasLines();

    /**
     * Returns the lines.
     *
     * @return \Doctrine\Common\Collections\Collection|DocumentLineInterface[]
     */
    public function getLines();

    /**
     * Returns the lines with the given type.
     *
     * @param string $type
     *
     * @return array|DocumentLineInterface[]
     */
    public function getLinesByType($type);

    /**
     * Returns whether the invoice has the line or not.
     *
     * @param DocumentLineInterface $line
     *
     * @return bool
     */
    public function hasLine(DocumentLineInterface $line);

    /**
     * Returns whether the invoice has at least one with the given type.
     *
     * @param string $type
     *
     * @return bool
     */
    public function hasLineByType($type);

    /**
     * Adds the line.
     *
     * @param DocumentLineInterface $line
     *
     * @return $this|DocumentInterface
     */
    public function addLine(DocumentLineInterface $line);

    /**
     * Removes the line.
     *
     * @param DocumentLineInterface $line
     *
     * @return $this|DocumentInterface
     */
    public function removeLine(DocumentLineInterface $line);

    /**
     * Sets the document lines.
     *
     * @param ArrayCollection|DocumentLineInterface[] $lines
     *
     * @return $this|DocumentInterface
     */
    public function setLines(ArrayCollection $lines);

    /**
     * Returns the comment.
     *
     * @return string
     */
    public function getComment();

    /**
     * Sets the comment.
     *
     * @param string $comment
     *
     * @return $this|DocumentInterface
     */
    public function setComment($comment);

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
     * @return $this|DocumentInterface
     */
    public function setDescription($description);

    /**
     * Returns the goods base (after discounts).
     *
     * @return float
     */
    public function getGoodsBase();

    /**
     * Sets the goods base (after discounts).
     *
     * @param float $base
     *
     * @return $this|DocumentInterface
     */
    public function setGoodsBase($base);

    /**
     * Returns the discount base.
     *
     * @return float
     */
    public function getDiscountBase();

    /**
     * Sets the discount base.
     *
     * @param float $base
     *
     * @return Document
     */
    public function setDiscountBase($base);

    /**
     * Returns the shipment base.
     *
     * @return float
     */
    public function getShipmentBase();

    /**
     * Sets the shipment base.
     *
     * @param float $base
     *
     * @return $this|DocumentInterface
     */
    public function setShipmentBase($base);

    /**
     * Returns the taxes total.
     *
     * @return float
     */
    public function getTaxesTotal();

    /**
     * Sets the taxes total.
     *
     * @param float $total
     *
     * @return $this|DocumentInterface
     */
    public function setTaxesTotal($total);

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
     * @return $this|DocumentInterface
     */
    public function setTaxesDetails(array $details);

    /**
     * Returns the grand total.
     *
     * @return float
     */
    public function getGrandTotal();

    /**
     * Sets the grand total.
     *
     * @param float $total
     *
     * @return $this|DocumentInterface
     */
    public function setGrandTotal($total);

    /**
     * Returns whether the document has at least one line discount.
     *
     * @return bool
     */
    public function hasLineDiscount();

    /**
     * Returns whether the document has multiple taxes.
     *
     * @return bool
     */
    public function hasMultipleTaxes();

}
