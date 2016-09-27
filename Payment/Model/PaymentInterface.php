<?php

namespace Ekyna\Component\Commerce\Payment\Model;

use Ekyna\Component\Commerce\Common\Model;
use Ekyna\Component\Resource\Model as ResourceModel;

/**
 * Interface PaymentInterface
 * @package Ekyna\Component\Commerce\Payment\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface PaymentInterface extends
    ResourceModel\ResourceInterface,
    ResourceModel\TimestampableInterface,
    Model\NumberSubjectInterface,
    Model\StateSubjectInterface
{
    /**
     * Returns the sale.
     *
     * @return Model\SaleInterface
     */
    public function getSale();

    /**
     * Returns the currency.
     *
     * @return Model\CurrencyInterface
     */
    public function getCurrency();

    /**
     * Sets the currency.
     *
     * @param Model\CurrencyInterface $currency
     *
     * @return $this|PaymentInterface
     */
    public function setCurrency(Model\CurrencyInterface $currency);

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
     * Returns the details.
     *
     * @return array
     */
    public function getDetails();

    /**
     * Sets the details.
     *
     * @param array $details
     *
     * @return $this|PaymentInterface
     */
    public function setDetails(array $details);

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
     * @return $this|PaymentInterface
     */
    public function setDescription($description);

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
