<?php

namespace Ekyna\Component\Commerce\Common\Model;

use Doctrine\Common\Collections\Collection;
use Ekyna\Component\Commerce\Customer\Model\CustomerInterface;
use Ekyna\Component\Resource\Model\ResourceInterface;

/**
 * Interface SaleInterface
 * @package Ekyna\Component\Commerce\Common\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface SaleInterface extends ResourceInterface, AdjustableInterface
{
    /**
     * Returns the customer.
     *
     * @return CustomerInterface
     */
    public function getCustomer();

    /**
     * Sets the customer.
     *
     * @param CustomerInterface $customer
     *
     * @return $this|SaleInterface
     */
    public function setCustomer(CustomerInterface $customer);

    /**
     * Returns the company.
     *
     * @return string
     */
    public function getCompany();

    /**
     * Sets the company.
     *
     * @param string $company
     *
     * @return $this|SaleInterface
     */
    public function setCompany($company);

    /**
     * Returns the firstName.
     *
     * @return string
     */
    public function getFirstName();

    /**
     * Sets the firstName.
     *
     * @param string $firstName
     *
*@return $this|SaleInterface
     */
    public function setFirstName($firstName);

    /**
     * Returns the lastName.
     *
     * @return string
     */
    public function getLastName();

    /**
     * Sets the lastName.
     *
     * @param string $lastName
     *
*@return $this|SaleInterface
     */
    public function setLastName($lastName);

    /**
     * Returns the email.
     *
     * @return string
     */
    public function getEmail();

    /**
     * Sets the email.
     *
     * @param string $email
     *
*@return $this|SaleInterface
     */
    public function setEmail($email);

    /**
     * Returns the invoice address.
     *
     * @return AddressInterface
     */
    public function getInvoiceAddress();

    /**
     * Returns the delivery address.
     *
     * @return AddressInterface
     */
    public function getDeliveryAddress();

    /**
     * Returns whether the invoice address is used as delivery address or not.
     *
     * @return boolean
     */
    public function getSameAddress();

    /**
     * Sets whether to use the invoice address as delivery address or not.
     *
     * @param boolean $sameAddress
     *
     * @return $this|SaleInterface
     */
    public function setSameAddress($sameAddress);

    /**
     * Returns the currency.
     *
     * @return CurrencyInterface
     */
    public function getCurrency();

    /**
     * Sets the currency.
     *
     * @param CurrencyInterface $currency
     *
     * @return $this|SaleInterface
     */
    public function setCurrency(CurrencyInterface $currency);

    /**
     * Returns whether the transaction has items or not.
     *
     * @return bool
     */
    public function hasItems();

    /**
     * Returns the items.
     *
     * @return Collection|SaleItemInterface[]
     */
    public function getItems();

    /**
     * Returns the weight total.
     *
     * @return float
     */
    public function getWeightTotal();

    /**
     * Sets the weight total.
     *
     * @param float $weightTotal
     *
     * @return $this|SaleInterface
     */
    public function setWeightTotal($weightTotal);

    /**
     * Returns the net total.
     *
     * @return float
     */
    public function getNetTotal();

    /**
     * Sets the net total.
     *
     * @param float $netTotal
     *
     * @return $this|SaleInterface
     */
    public function setNetTotal($netTotal);

    /**
     * Returns the adjustment total.
     *
     * @return float
     */
    public function getAdjustmentTotal();

    /**
     * Sets the adjustment total.
     *
     * @param float $adjustmentTotal
     *
     * @return $this|SaleInterface
     */
    public function setAdjustmentTotal($adjustmentTotal);

    /**
     * Returns the total.
     *
     * @return float
     */
    public function getGrandTotal();

    /**
     * Sets the total.
     *
     * @param float $total
     *
     * @return $this|SaleInterface
     */
    public function setGrandTotal($total);

    /**
     * Returns the "created at" datetime.
     *
     * @return \DateTime
     */
    public function getCreatedAt();

    /**
     * Sets the "created at" datetime.
     *
     * @param \DateTime $createdAt
     *
     * @return $this|SaleInterface
     */
    public function setCreatedAt(\DateTime $createdAt);

    /**
     * Returns the "updated at" datetime.
     *
     * @return \DateTime
     */
    public function getUpdatedAt();

    /**
     * Sets the "updated at" datetime.
     *
     * @param \DateTime $updatedAt
     *
     * @return $this|SaleInterface
     */
    public function setUpdatedAt(\DateTime $updatedAt = null);
}
