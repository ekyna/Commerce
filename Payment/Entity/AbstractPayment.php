<?php

namespace Ekyna\Component\Commerce\Payment\Entity;

use Ekyna\Component\Commerce\Common\Model as Common;
use Ekyna\Component\Commerce\Payment\Model as Payment;
use Ekyna\Component\Resource\Model\TimestampableTrait;

/**
 * Class AbstractPayment
 * @package Ekyna\Component\Commerce\Payment\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
abstract class AbstractPayment implements Payment\PaymentInterface
{
    use Common\ExchangeSubjectTrait;
    use Common\KeySubjectTrait;
    use Common\NumberSubjectTrait;
    use Common\StateSubjectTrait;
    use TimestampableTrait;

    /**
     * @var int
     */
    protected $id;

    /**
     * @var bool
     */
    protected $refund;

    /**
     * @var Payment\PaymentMethodInterface
     */
    protected $method;

    /**
     * The amount in payment currency
     *
     * @var float
     */
    protected $amount;

    /**
     * The amount in default currency
     *
     * @var float
     */
    protected $realAmount;

    /**
     * @var array
     */
    protected $details;

    /**
     * @var string
     */
    protected $description;

    /**
     * @var \DateTime
     */
    protected $completedAt;


    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->refund = false;
        $this->amount = 0;
        $this->realAmount = 0;

        $this->clear();
    }

    /**
     * Returns the string representation.
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->number ?: 'New payment';
    }

    /**
     * Clones the payment.
     */
    public function __clone()
    {
        $this->clear();
    }

    /**
     * Clears the payment data.
     */
    protected function clear()
    {
        $this->id = null;
        $this->state = Payment\PaymentStates::STATE_NEW;
        $this->details = [];
        $this->key = null;
        $this->number = null;
        $this->description = null;
        $this->completedAt = null;
        $this->createdAt = null;
        $this->updatedAt = null;
    }

    /**
     * @inheritdoc
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @inheritDoc
     */
    public function isRefund(): bool
    {
        return $this->refund;
    }

    /**
     * @inheritDoc
     */
    public function setRefund(bool $refund): Payment\PaymentInterface
    {
        $this->refund = $refund;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * @inheritdoc
     */
    public function setMethod(Payment\PaymentMethodInterface $method)
    {
        $this->method = $method;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getAmount(): ?float
    {
        return $this->amount;
    }

    /**
     * @inheritdoc
     */
    public function setAmount(float $amount)
    {
        $this->amount = $amount;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getRealAmount(): ?float
    {
        return $this->realAmount;
    }

    /**
     * @inheritdoc
     */
    public function setRealAmount(float $amount)
    {
        $this->realAmount = $amount;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getDetails()
    {
        return $this->details;
    }

    /**
     * @inheritdoc
     */
    public function setDetails($details)
    {
        if ($details instanceof \Traversable) {
            $details = iterator_to_array($details);
        }

        $this->details = $details;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @inheritdoc
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getCompletedAt()
    {
        return $this->completedAt;
    }

    /**
     * @inheritdoc
     */
    public function setCompletedAt(\DateTime $completedAt = null)
    {
        $this->completedAt = $completedAt;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->details);
    }

    /**
     * @inheritDoc
     */
    public function offsetExists($offset)
    {
        return isset($this->details[$offset]);
    }

    /**
     * @inheritDoc
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
    public function offsetSet($offset, $value)
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
    public function offsetUnset($offset)
    {
        unset($this->details[$offset]);
    }
}
