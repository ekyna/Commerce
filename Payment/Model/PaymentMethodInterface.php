<?php

namespace Ekyna\Component\Commerce\Payment\Model;

use Ekyna\Component\Commerce\Common\Model\CurrencyInterface;
use Ekyna\Component\Commerce\Common\Model\MethodInterface;

/**
 * Interface PaymentMethodInterface
 * @package Ekyna\Component\Commerce\Payment\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface PaymentMethodInterface extends MethodInterface
{
    /**
     * Returns whether this method has authorized currencies.
     *
     * @return bool
     */
    public function hasCurrencies();

    /**
     * Returns whether this method authorizes the given currency.
     *
     * @param CurrencyInterface $currency
     *
     * @return bool
     */
    public function hasCurrency(CurrencyInterface $currency);

    /**
     * Adds the authorized currency.
     *
     * @param CurrencyInterface $currency
     *
     * @return $this|PaymentMethodInterface
     */
    public function addCurrency(CurrencyInterface $currency);

    /**
     * Removes the authorized currency.
     *
     * @param CurrencyInterface $currency
     *
     * @return $this|PaymentMethodInterface
     */
    public function removeCurrency(CurrencyInterface $currency);

    /**
     * Returns the authorized currencies.
     *
     * @return \Doctrine\Common\Collections\ArrayCollection|CurrencyInterface[]
     */
    public function getCurrencies();

    /**
     * Returns whether or not the method requires manual management of payments state.
     *
     * @return bool
     */
    public function isManual();

    /**
     * Returns whether or not the method results in an customer credit balance payment.
     *
     * @return bool
     */
    public function isCredit();

    /**
     * Returns whether or not the method results in an customer outstanding balance payment.
     *
     * @return bool
     */
    public function isOutstanding();
}
