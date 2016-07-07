<?php

namespace Ekyna\Component\Commerce\Payment\Model;

use Ekyna\Component\Commerce\Order\Model\OrderInterface;
use Ekyna\Component\Commerce\Pricing\Model\CurrencyInterface;

/**
 * Interface PaymentInterface
 * @package Ekyna\Component\Commerce\Payment\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface PaymentInterface
{
    /**
     * Returns the id.
     *
     * @return int
     */
    public function getId();

    /**
     * Returns the number.
     *
     * @return string
     */
    public function getNumber();

    /**
     * Sets the number.
     *
     * @param string $number
     *
     * @return $this|PaymentInterface
     */
    public function setNumber($number);

    /**
     * Returns the order.
     *
     * @return OrderInterface
     */
    public function getOrder();

    /**
     * Sets the order.
     *
     * @param OrderInterface $order
     *
     * @return $this|PaymentInterface
     */
    public function setOrder(OrderInterface $order);

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
     * @return $this|PaymentInterface
     */
    public function setCurrency(CurrencyInterface $currency);

    /**
     * Returns the method.
     *
     * @return PaymentMethodInterface
     */
    public function getMethod();

    /**
     * Sets the method.
     *
     * @param PaymentMethodInterface $method
     *
     * @return $this|PaymentInterface
     */
    public function setMethod(PaymentMethodInterface $method);

    /**
     * Returns the amount.
     *
     * @return float
     */
    public function getAmount();

    /**
     * Sets the amount.
     *
     * @param float $amount
     *
     * @return $this|PaymentInterface
     */
    public function setAmount($amount);

    /**
     * Returns the state.
     *
     * @return string
     */
    public function getState();

    /**
     * Sets the state.
     *
     * @param string $state
     *
     * @return $this|PaymentInterface
     */
    public function setState($state);

    /**
     * Returns the createdAt.
     *
     * @return \DateTime
     */
    public function getCreatedAt();

    /**
     * Sets the createdAt.
     *
     * @param \DateTime $createdAt
     *
     * @return $this|PaymentInterface
     */
    public function setCreatedAt(\DateTime $createdAt = null);

    /**
     * Returns the updateAt.
     *
     * @return \DateTime
     */
    public function getUpdatedAt();

    /**
     * Sets the updateAt.
     *
     * @param \DateTime $updateAt
     *
     * @return $this|PaymentInterface
     */
    public function setUpdatedAt(\DateTime $updateAt = null);

    /**
     * Returns the completedAt.
     *
     * @return \DateTime
     */
    public function getCompletedAt();

    /**
     * Sets the completedAt.
     *
     * @param \DateTime $completedAt
     *
     * @return $this|PaymentInterface
     */
    public function setCompletedAt(\DateTime $completedAt = null);
}
