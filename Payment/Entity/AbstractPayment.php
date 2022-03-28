<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Payment\Entity;

use ArrayIterator;
use DateTime;
use DateTimeInterface;
use Decimal\Decimal;
use Ekyna\Component\Commerce\Common\Model as Common;
use Ekyna\Component\Commerce\Payment\Model as Payment;
use Ekyna\Component\Resource\Model\AbstractResource;
use Ekyna\Component\Resource\Model\TimestampableTrait;
use Traversable;

/**
 * Class AbstractPayment
 * @package Ekyna\Component\Commerce\Payment\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
abstract class AbstractPayment extends AbstractResource implements Payment\PaymentInterface
{
    use Common\ExchangeSubjectTrait;
    use Common\KeySubjectTrait;
    use Common\NumberSubjectTrait;
    use Common\StateSubjectTrait;
    use TimestampableTrait;

    protected bool                            $refund;
    protected ?Payment\PaymentMethodInterface $method      = null;
    protected Decimal                         $amount;              // payment currency
    protected Decimal                         $realAmount;          // default currency
    protected array                           $details;
    protected ?string                         $description = null;
    protected ?DateTimeInterface              $completedAt = null;


    public function __construct()
    {
        $this->refund = false;
        $this->amount = new Decimal(0);
        $this->realAmount = new Decimal(0);

        $this->clear();
    }

    public function __toString(): string
    {
        return $this->number ?: 'New payment';
    }

    public function __clone()
    {
        parent::__clone();

        $this->clear();
    }

    /**
     * Clears the payment data.
     */
    protected function clear()
    {
        $this->state = Payment\PaymentStates::STATE_NEW;
        $this->details = [];
        $this->key = null;
        $this->number = null;
        $this->description = null;
        $this->completedAt = null;
        $this->createdAt = new DateTime();
        $this->updatedAt = null;
    }

    public function isRefund(): bool
    {
        return $this->refund;
    }

    public function setRefund(bool $refund): Payment\PaymentInterface
    {
        $this->refund = $refund;

        return $this;
    }

    public function getMethod(): ?Payment\PaymentMethodInterface
    {
        return $this->method;
    }

    public function setMethod(?Payment\PaymentMethodInterface $method): Payment\PaymentInterface
    {
        $this->method = $method;

        return $this;
    }

    public function getAmount(): ?Decimal
    {
        return $this->amount;
    }

    public function setAmount(Decimal $amount): Payment\PaymentInterface
    {
        $this->amount = $amount;

        return $this;
    }

    public function getRealAmount(): ?Decimal
    {
        return $this->realAmount;
    }

    public function setRealAmount(Decimal $amount): Payment\PaymentInterface
    {
        $this->realAmount = $amount;

        return $this;
    }

    /**
     * @inheritDoc
     *
     * @return array
     */
    public function getDetails()
    {
        return $this->details;
    }

    /**
     * @inheritDoc
     */
    public function setDetails($details): void
    {
        if ($details instanceof Traversable) {
            $details = iterator_to_array($details);
        }

        $this->details = $details;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): Payment\PaymentInterface
    {
        $this->description = $description;

        return $this;
    }

    public function getCompletedAt(): ?DateTimeInterface
    {
        return $this->completedAt;
    }

    public function setCompletedAt(?DateTimeInterface $date): Payment\PaymentInterface
    {
        $this->completedAt = $date;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->details);
    }

    /**
     * @inheritDoc
     */
    public function offsetExists($offset): bool
    {
        return isset($this->details[$offset]);
    }

    /**
     * @inheritDoc
     *
     * @return mixed
     */
    public function offsetGet($offset)
    {
        if ($this->details[$offset]) {
            return $this->details[$offset];
        }

        return null;
    }

    /**
     * @inheritDoc
     */
    public function offsetSet($offset, $value): void
    {
        if (is_null($offset)) {
            $this->details[] = $value;
        } else {
            $this->details[$offset] = $value;
        }
    }

    /**
     * @inheritDoc
     */
    public function offsetUnset($offset): void
    {
        unset($this->details[$offset]);
    }
}
