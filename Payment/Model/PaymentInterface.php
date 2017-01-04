<?php

namespace Ekyna\Component\Commerce\Payment\Model;

use Ekyna\Component\Commerce\Common\Model as Common;
use Ekyna\Component\Resource\Model as ResourceModel;
use Payum\Core\Model as Payum;

/**
 * Interface PaymentInterface
 * @package Ekyna\Component\Commerce\Payment\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 *
 * @method $this|PaymentInterface setDetails($details)
 */
interface PaymentInterface extends
    ResourceModel\ResourceInterface,
    ResourceModel\TimestampableInterface,
    Common\NumberSubjectInterface,
    Common\KeySubjectInterface,
    Common\CurrencySubjectInterface,
    Common\StateSubjectInterface,
    Payum\DetailsAggregateInterface,
    Payum\DetailsAwareInterface,
    \ArrayAccess,
    \IteratorAggregate
{
    /**
     * Returns the sale.
     *
     * @return Common\SaleInterface
     */
    public function getSale();

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
