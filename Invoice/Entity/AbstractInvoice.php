<?php

namespace Ekyna\Component\Commerce\Invoice\Entity;

use Ekyna\Component\Commerce\Common\Model\NumberSubjectTrait;
use Ekyna\Component\Commerce\Document\Model\Document;
use Ekyna\Component\Commerce\Invoice\Model as Invoice;
use Ekyna\Component\Commerce\Payment\Model\PaymentMethodInterface;
use Ekyna\Component\Resource\Model\TimestampableTrait;

/**
 * Class AbstractInvoice
 * @package Ekyna\Component\Commerce\Invoice\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
abstract class AbstractInvoice extends Document implements Invoice\InvoiceInterface
{
    use NumberSubjectTrait,
        TimestampableTrait;

    /**
     * @var int
     */
    protected $id;

    /**
     * The paid total (document currency).
     *
     * @var float
     */
    protected $paidTotal;

    /**
     * The paid total (default currency).
     *
     * @var float
     */
    protected $realPaidTotal;

    /**
     * @var \DateTime
     */
    protected $dueDate;

    /**
     * @var PaymentMethodInterface
     */
    protected $paymentMethod;


    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->createdAt = new \DateTime();
        $this->type = Invoice\InvoiceTypes::TYPE_INVOICE;
        $this->paidTotal = 0;
        $this->realPaidTotal = 0;
    }

    /**
     * @inheritDoc
     */
    public function __clone()
    {
        if ($this->id) {
            $this->id = null;
        }
    }

    /**
     * @inheritDoc
     */
    public function __toString(): string
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
    public function getPaidTotal(): float
    {
        return $this->paidTotal;
    }

    /**
     * @inheritdoc
     */
    public function setPaidTotal(float $amount): Invoice\InvoiceInterface
    {
        $this->paidTotal = $amount;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getRealPaidTotal(): float
    {
        return $this->realPaidTotal;
    }

    /**
     * @inheritDoc
     */
    public function setRealPaidTotal(float $amount): Invoice\InvoiceInterface
    {
        $this->realPaidTotal = $amount;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getDueDate(): ?\DateTime
    {
        return $this->dueDate;
    }

    /**
     * @inheritdoc
     */
    public function setDueDate(\DateTime $dueDate = null): Invoice\InvoiceInterface
    {
        $this->dueDate = $dueDate;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setPaymentMethod(PaymentMethodInterface $method = null): Invoice\InvoiceInterface
    {
        $this->paymentMethod = $method;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getPaymentMethod(): ?PaymentMethodInterface
    {
        return $this->paymentMethod;
    }
}
