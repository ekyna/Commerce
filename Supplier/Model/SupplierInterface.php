<?php

namespace Ekyna\Component\Commerce\Supplier\Model;

use Ekyna\Component\Commerce\Common\Model\CurrencyInterface;
use Ekyna\Component\Commerce\Common\Model\IdentityInterface;
use Ekyna\Component\Commerce\Pricing\Model\TaxInterface;
use Ekyna\Component\Resource\Model\ResourceInterface;

/**
 * Class SupplierInterface
 * @package Ekyna\Component\Commerce\Supplier\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface SupplierInterface extends ResourceInterface, IdentityInterface
{
    /**
     * Returns the name.
     *
     * @return string
     */
    public function getName();

    /**
     * Sets the name.
     *
     * @param string $name
     *
     * @return $this|SupplierInterface
     */
    public function setName($name);

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
     * @return $this|SupplierInterface
     */
    public function setEmail($email);

    /**
     * Returns the customer code.
     *
     * @return string
     */
    public function getCustomerCode();

    /**
     * Sets the customer code.
     *
     * @param string $code
     *
     * @return $this|SupplierInterface
     */
    public function setCustomerCode($code);

    /**
     * Returns the address.
     *
     * @return SupplierAddressInterface
     */
    public function getAddress();

    /**
     * Sets the address.
     *
     * @param SupplierAddressInterface $address
     *
     * @return $this|SupplierInterface
     */
    public function setAddress(SupplierAddressInterface $address = null);

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
     * @return $this|SupplierInterface
     */
    public function setCurrency(CurrencyInterface $currency);

    /**
     * Returns the tax.
     *
     * @return TaxInterface
     */
    public function getTax();

    /**
     * Sets the tax.
     *
     * @param TaxInterface $tax
     *
     * @return $this|SupplierInterface
     */
    public function setTax(TaxInterface $tax = null);

    /**
     * Returns the carrier.
     *
     * @return SupplierCarrierInterface
     */
    public function getCarrier();

    /**
     * Sets the carrier.
     *
     * @param SupplierCarrierInterface $carrier
     *
     * @return $this|SupplierInterface
     */
    public function setCarrier(SupplierCarrierInterface $carrier = null);
}
