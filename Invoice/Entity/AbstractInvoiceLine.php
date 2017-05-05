<?php

namespace Ekyna\Component\Commerce\Invoice\Entity;

use Ekyna\Component\Commerce\Invoice\Model;
use Ekyna\Component\Commerce\Subject\Model\SubjectRelativeTrait;

/**
 * Class AbstractInvoiceLine
 * @package Ekyna\Component\Commerce\Invoice\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
abstract class AbstractInvoiceLine implements Model\InvoiceLineInterface
{
    use SubjectRelativeTrait;

    /**
     * @var int
     */
    protected $id;

    /**
     * @var Model\InvoiceInterface
     */
    protected $invoice;

    /**
     * @var string
     */
    protected $type;

    /**
     * @var string
     */
    protected $designation;

    /**
     * @var string
     */
    protected $reference;

    /**
     * @var float
     */
    protected $netPrice;

    /**
     * @var array
     */
    protected $taxesDetails;

    /**
     * @var float
     */
    protected $quantity;


    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->initializeSubjectIdentity();

        $this->netPrice = 0;
        $this->taxesDetails = [];
        $this->quantity = 1;
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
    public function getInvoice()
    {
        return $this->invoice;
    }

    /**
     * @inheritdoc
     */
    public function setInvoice(Model\InvoiceInterface $invoice = null)
    {
        if ($this->invoice !== $invoice) {
            $previous = $this->invoice;
            $this->invoice = $invoice;

            if ($previous) {
                $previous->removeLine($this);
            }

            if ($this->invoice) {
                $this->invoice->addLine($this);
            }
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @inheritdoc
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getDesignation()
    {
        return $this->designation;
    }

    /**
     * @inheritdoc
     */
    public function setDesignation($designation)
    {
        $this->designation = $designation;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getReference()
    {
        return $this->reference;
    }

    /**
     * @inheritdoc
     */
    public function setReference($reference)
    {
        $this->reference = $reference;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getNetPrice()
    {
        return $this->netPrice;
    }

    /**
     * @inheritdoc
     */
    public function setNetPrice($price)
    {
        $this->netPrice = (float)$price;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getTaxesDetails()
    {
        return $this->taxesDetails;
    }

    /**
     * @inheritdoc
     */
    public function setTaxesDetails(array $details)
    {
        $this->taxesDetails = $details;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getQuantity()
    {
        return $this->quantity;
    }

    /**
     * @inheritdoc
     */
    public function setQuantity($quantity)
    {
        $this->quantity = (float)$quantity;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getBaseTotal()
    {
        return $this->netPrice * $this->quantity;
    }

    /**
     * @inheritdoc
     */
    public function getTaxesTotal()
    {
        $total = 0;

        foreach ($this->taxesDetails as $tax) {
            $total += $tax['amount'] * $this->quantity;
        }

        return $total;
    }

    /**
     * @inheritdoc
     */
    public function getTotal()
    {
        return $this->getBaseTotal() + $this->getTaxesTotal();
    }
}
