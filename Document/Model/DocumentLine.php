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
    protected $netPrice;

    /**
     * @var float
     */
    protected $quantity;

    /**
     * @var float
     */
    protected $baseTotal;

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
        $this->netPrice = 0;
        $this->quantity = 0;
        $this->discountTotal = 0;
        $this->netTotal = 0;
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
        $this->document = $document;

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
    public function getBaseTotal()
    {
        return $this->baseTotal;
    }

    /**
     * @inheritdoc
     */
    public function setBaseTotal($baseTotal)
    {
        $this->baseTotal = $baseTotal;

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
