<?php

namespace Ekyna\Component\Commerce\Document\Model;

use Ekyna\Component\Commerce\Common\Model as Common;

/**
 * Class DocumentLine
 * @package Ekyna\Component\Commerce\Common\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class DocumentLine implements DocumentLineInterface
{
    /**
     * @var Document
     */
    protected $document;

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
    protected $unit;

    /**
     * @var float
     */
    protected $quantity;

    /**
     * @var float
     */
    protected $gross;

    /**
     * @var float
     */
    protected $discount;

    /**
     * @var array
     */
    protected $discountRates;

    /**
     * @var float
     */
    protected $base;

    /**
     * @var float
     */
    protected $tax;

    /**
     * @var array
     */
    protected $taxRates;

    /**
     * @var float
     */
    protected $total;

    /**
     * @var Common\SaleItemInterface
     */
    protected $saleItem;

    /**
     * @var Common\AdjustmentInterface
     */
    protected $saleAdjustment;


    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->unit = 0;
        $this->quantity = 0;
        $this->discount = 0;
        $this->base = 0;
        $this->taxRates = [];
    }

    /**
     * @inheritdoc
     */
    public function getDocument()
    {
        return $this->document;
    }

    /**
     * @inheritdoc
     */
    public function setDocument(DocumentInterface $document = null)
    {
        if ($this->document !== $document) {
            if ($previous = $this->document) {
                $this->document = null;
                $previous->removeLine($this);
            }

            if ($this->document = $document) {
                $this->document->addLine($this);
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
    public function getUnit()
    {
        return $this->unit;
    }

    /**
     * @inheritdoc
     */
    public function setUnit($price)
    {
        $this->unit = (float)$price;

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
    public function getGross()
    {
        return $this->gross;
    }

    /**
     * @inheritdoc
     */
    public function setGross($gross)
    {
        $this->gross = (float)$gross;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getDiscount()
    {
        return $this->discount;
    }

    /**
     * @inheritdoc
     */
    public function setDiscount($total)
    {
        $this->discount = (float)$total;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getDiscountRates()
    {
        return $this->discountRates;
    }

    /**
     * @inheritdoc
     */
    public function setDiscountRates(array $rates)
    {
        $this->discountRates = $rates;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getBase()
    {
        return $this->base;
    }

    /**
     * @inheritdoc
     */
    public function setBase($total)
    {
        $this->base = (float)$total;

        return $this;
    }

    /**
     * Returns the tax.
     *
     * @return float
     */
    public function getTax()
    {
        return $this->tax;
    }

    /**
     * Sets the tax.
     *
     * @param float $tax
     *
     * @return DocumentLine
     */
    public function setTax($tax)
    {
        $this->tax = (float)$tax;

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
    public function setTaxRates(array $rates)
    {
        $this->taxRates = $rates;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getTotal()
    {
        return $this->total;
    }

    /**
     * @inheritdoc
     */
    public function setTotal($total)
    {
        $this->total = (float)$total;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getSale()
    {
        return $this->getDocument()->getSale();
    }

    /**
     * @inheritdoc
     */
    public function getSaleItem()
    {
        return $this->saleItem;
    }

    /**
     * @inheritdoc
     */
    public function setSaleItem(Common\SaleItemInterface $item = null)
    {
        $this->saleItem = $item;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getSaleAdjustment()
    {
        return $this->saleAdjustment;
    }

    /**
     * @inheritdoc
     */
    public function setSaleAdjustment(Common\AdjustmentInterface $adjustment = null)
    {
        $this->saleAdjustment = $adjustment;

        return $this;
    }
}
