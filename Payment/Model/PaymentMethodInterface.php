<?php

namespace Ekyna\Component\Commerce\Payment\Model;

use Doctrine\Common\Collections\Collection;
use Ekyna\Component\Commerce\Common\Model\CurrencyInterface;
use Ekyna\Component\Commerce\Common\Model\MethodInterface;
use Ekyna\Component\Commerce\Payment\Entity\PaymentMethodTranslation;

/**
 * Interface PaymentMethodInterface
 * @package Ekyna\Component\Commerce\Payment\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 *
 * @method PaymentMethodTranslation translate($locale = null, $create = false)
 * @method Collection|PaymentMethodTranslation[] getTranslations()
 */
interface PaymentMethodInterface extends MethodInterface
{
    /**
     * Returns whether this method has authorized currencies.
     *
     * @return bool
     */
    public function hasCurrencies(): bool;

    /**
     * Returns whether this method authorizes the given currency.
     *
     * @param CurrencyInterface $currency
     *
     * @return bool
     */
    public function hasCurrency(CurrencyInterface $currency): bool;

    /**
     * Adds the authorized currency.
     *
     * @param CurrencyInterface $currency
     *
     * @return $this|PaymentMethodInterface
     */
    public function addCurrency(CurrencyInterface $currency): PaymentMethodInterface;

    /**
     * Removes the authorized currency.
     *
     * @param CurrencyInterface $currency
     *
     * @return $this|PaymentMethodInterface
     */
    public function removeCurrency(CurrencyInterface $currency): PaymentMethodInterface;

    /**
     * Returns the authorized currencies.
     *
     * @return Collection|CurrencyInterface[]
     */
    public function getCurrencies(): Collection;

    /**
     * Returns whether to use the default currency.
     *
     * @return bool
     */
    public function isDefaultCurrency(): bool;

    /**
     * Sets whether to use the default currency.
     *
     * @param bool $default
     *
     * @return PaymentMethodInterface
     */
    public function setDefaultCurrency(bool $default): PaymentMethodInterface;

    /**
     * Returns whether this method is private.
     *
     * @return bool
     */
    public function isPrivate(): bool;

    /**
     * Sets whether this method is private.
     *
     * @param bool $private
     *
     * @return PaymentMethodInterface
     */
    public function setPrivate(bool $private): PaymentMethodInterface;

    /**
     * Returns whether or not the method requires manual management of payments state.
     *
     * @return bool
     */
    public function isManual(): bool;

    /**
     * Returns whether or not the method results in an customer credit balance payment.
     *
     * @return bool
     */
    public function isCredit(): bool;

    /**
     * Returns whether or not the method results in an customer outstanding balance payment.
     *
     * @return bool
     */
    public function isOutstanding(): bool;
}
