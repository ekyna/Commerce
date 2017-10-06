<?php

namespace Ekyna\Component\Commerce\Invoice\Entity;

use Ekyna\Component\Commerce\Invoice\Model;

/**
 * Class AbstractInvoiceLine
 * @package Ekyna\Component\Commerce\Invoice\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
abstract class AbstractInvoiceLine implements Model\InvoiceLineInterface
{
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
    protected $description;

    /**
     * @var string
     */
    protected $reference;

    /**
     * @var float
     */
    protected $netPrice;

    /**
     * @var float
     */
    protected $quantity;

    /**
     * @var float
     */
    protected $discountTotal;

    /**
     * @var float
     */
    protected $netTotal;

    /**
     * @var array
     */
    protected $taxRates;


    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->netPrice = 0;
        $this->quantity = 0;
        $this->discountTotal = 0;
        $this->netTotal = 0;
        $this->taxRates = [];
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
    public function getDiscountTotal()
    {
        return $this->discountTotal;
    }

    /**
     * @inheritdoc
     */
    public function setDiscountTotal($total)
    {
        $this->discountTotal = $total;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getNetTotal()
    {
        return $this->netTotal;
    }

    /**
     * @inheritdoc
     */
    public function setNetTotal($total)
    {
        $this->netTotal = $total;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getTaxRates()
    {
        return $this->taxRates;
    }

    /**
     * @inheritdoc
     */
    public function setTaxRates(array $taxRates)
    {
        $this->taxRates = $taxRates;

        return $this;
    }
}
