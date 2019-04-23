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
    use Common\KeySubjectTrait,
        Common\NumberSubjectTrait,
        Common\ExchangeSubjectTrait,
        Common\StateSubjectTrait,
        TimestampableTrait;

    /**
     * @var int
     */
    protected $id;

    /**
     * @var string
     *
     * TODO 'payment' or 'refund'
     * @see \Ekyna\Component\Commerce\Invoice\Resolver\InvoicePaymentResolver
     * @see \Ekyna\Component\Commerce\Accounting\Export\AccountingExporter
     * @see and many others...
     */
    protected $type;

    /**
     * @var Payment\PaymentMethodInterface
     */
    protected $method;

    /**
     * @var float
     */
    protected $amount;

    /**
     * @var array
     */
    protected $details;

    /**
     * @var string
     */
    protected $description;


    protected $exchangeRate;

    /**
     * @var \DateTime
     */
    protected $completedAt;


    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->amount = 0;
        $this->state = Payment\PaymentStates::STATE_NEW;
        $this->details = [];
    }

    /**
     * Returns the string representation.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->getNumber();
    }

    /**
     * @inheritdoc
     */
    public function getId()
    {
        return $this->id;
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
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @inheritdoc
     */
    public function setAmount($amount)
    {
        $this->amount = (float)$amount;

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
