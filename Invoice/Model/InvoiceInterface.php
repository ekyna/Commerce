<?php

namespace Ekyna\Component\Commerce\Invoice\Model;

use Ekyna\Component\Commerce\Common\Model as Common;
use Ekyna\Component\Resource\Model as Resource;

/**
 * Interface InvoiceInterface
 * @package Ekyna\Component\Commerce\Invoice\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface InvoiceInterface extends
    Resource\ResourceInterface,
    Resource\TimestampableInterface,
    Common\NumberSubjectInterface
{
    /**
     * Returns the sale.
     *
     * @return Common\SaleInterface|InvoiceSubjectInterface
     */
    public function getSale();

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
     * @return $this|InvoiceInterface
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
     */
    public function setDeliveryAddress(array $data = null);

    /**
     * Returns whether the invoice has at least one line or not.
     *
     * @return bool
     */
    public function hasLines();

    /**
     * Returns the lines.
     *
     * @return \Doctrine\Common\Collections\Collection|InvoiceLineInterface[]
     */
    public function getLines();

    /**
     * Returns the lines with the given type.
     *
     * @param string $type
     *
     * @return array|InvoiceLineInterface[]
     */
    public function getLinesByType($type);

    /**
     * Returns whether the invoice has the line or not.
     *
     * @param InvoiceLineInterface $line
     *
     * @return bool
     */
    public function hasLine(InvoiceLineInterface $line);

    /**
     * Adds the line.
     *
     * @param InvoiceLineInterface $line
     *
     * @return $this|InvoiceInterface
     */
    public function addLine(InvoiceLineInterface $line);

    /**
     * Removes the line.
     *
     * @param InvoiceLineInterface $line
     *
     * @return $this|InvoiceInterface
     */
    public function removeLine(InvoiceLineInterface $line);

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
     * @return $this|InvoiceInterface
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
     * @return $this|InvoiceInterface
     */
    public function setGoodsBase($base);

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
     * @return $this|InvoiceInterface
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
     * @return $this|InvoiceInterface
     */
    public function setTaxesTotal($total);

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
     * @return $this|InvoiceInterface
     */
    public function setGrandTotal($total);

    /**
     * Returns the taxes details.
     *
     * @return array
     */
    public function getTaxesDetails();
}
