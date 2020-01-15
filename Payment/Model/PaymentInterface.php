<?php

namespace Ekyna\Component\Commerce\Payment\Model;

use Ekyna\Component\Commerce\Common\Model as Common;
use Ekyna\Component\Commerce\Payment\Entity\AbstractPayment;
use Ekyna\Component\Resource\Model as ResourceModel;
use Payum\Core\Model as Payum;

/**
 * Interface PaymentInterface
 * @package Ekyna\Component\Commerce\Payment\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface PaymentInterface extends
    ResourceModel\ResourceInterface,
    ResourceModel\TimestampableInterface,
    ResourceModel\LocalizedInterface,
    Common\NumberSubjectInterface,
    Common\KeySubjectInterface,
    Common\ExchangeSubjectInterface,
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
     * Returns whether this a refund payment.
     *
     * @return bool
     */
    public function isRefund(): bool;

    /**
     * Sets whether this a refund payment.
     *
     * @param bool $refund
     *
     * @return $this|PaymentInterface
     */
    public function setRefund(bool $refund): PaymentInterface;

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
     * Returns the amount (payment currency).
     *
     * @return float
     */
    public function getAmount(): ?float;

    /**
     * Sets the amount (payment currency).
     *
     * @param float $amount
     *
     * @return $this|PaymentInterface
     *
     * @internal Use payment updater
     *
     * @see \Ekyna\Component\Commerce\Payment\Updater\PaymentUpdaterInterface::updateAmount()
     */
    public function setAmount(float $amount);

    /**
     * Returns the real amount (default currency).
     *
     * @return float
     */
    public function getRealAmount(): ?float;

    /**
     * Sets the real amount (default currency).
     *
     * @param float $amount
     *
     * @return $this|PaymentInterface
     *
     * @internal Use payment updater
     *
     * @see \Ekyna\Component\Commerce\Payment\Updater\PaymentUpdaterInterface::updateRealAmount()
     */
    public function setRealAmount(float $amount);

    /**
     * Sets the details.
     *
     * @param object $details
     *
     * @return $this|PaymentInterface
     */
    public function setDetails($details);

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
     * Returns the "completed at" datetime.
     *
     * @return \DateTime
     */
    public function getCompletedAt();

    /**
     * Sets the "completed at" datetime.
     *
     * @param \DateTime $completedAt
     *
     * @return $this|PaymentInterface
     */
    public function setCompletedAt(\DateTime $completedAt = null);
}
