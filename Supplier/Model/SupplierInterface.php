<?php

namespace Ekyna\Component\Commerce\Supplier\Model;

use Ekyna\Component\Commerce\Common\Model\CurrencyInterface;
use Ekyna\Component\Commerce\Common\Model\IdentityInterface;
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
}
