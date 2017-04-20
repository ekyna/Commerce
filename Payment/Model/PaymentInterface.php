<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Payment\Model;

use ArrayAccess;
use DateTimeInterface;
use Decimal\Decimal;
use Ekyna\Component\Commerce\Common\Model as Common;
use Ekyna\Component\Resource\Model as ResourceModel;
use IteratorAggregate;
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
    ArrayAccess,
    IteratorAggregate
{
    public function getSale(): ?Common\SaleInterface;

    /**
     * Returns whether this is a refund payment.
     */
    public function isRefund(): bool;

    /**
     * Sets whether this is a refund payment.
     */
    public function setRefund(bool $refund): PaymentInterface;

    public function getMethod(): ?PaymentMethodInterface;

    public function setMethod(?PaymentMethodInterface $method): PaymentInterface;

    /**
     * Returns the amount (payment currency).
     *
     * @return Decimal
     */
    public function getAmount(): ?Decimal;

    /**
     * Sets the amount (payment currency).
     *
     * @internal Use payment updater
     *
     * @see \Ekyna\Component\Commerce\Payment\Updater\PaymentUpdaterInterface::updateAmount()
     */
    public function setAmount(Decimal $amount): PaymentInterface;

    /**
     * Returns the real amount (default currency).
     */
    public function getRealAmount(): ?Decimal;

    /**
     * Sets the real amount (default currency).
     *
     * @internal Use payment updater
     *
     * @see \Ekyna\Component\Commerce\Payment\Updater\PaymentUpdaterInterface::updateRealAmount()
     */
    public function setRealAmount(Decimal $amount): PaymentInterface;

    /**
     * @inheritDoc
     */
    public function setDetails($details): void;

    public function getDescription(): ?string;

    public function setDescription(?string $description): PaymentInterface;

    public function getCompletedAt(): ?DateTimeInterface;

    public function setCompletedAt(?DateTimeInterface $date): PaymentInterface;
}
