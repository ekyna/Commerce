<?php

namespace Acme\Product\Entity;

use Acme\Product\Provider\ProductProvider;
use Ekyna\Component\Commerce\Common\Model\Units;
use Ekyna\Component\Commerce\Pricing\Model as Pricing;
use Ekyna\Component\Commerce\Stock\Model as Stock;
use Ekyna\Component\Commerce\Subject\Model as Subject;

/**
 * Class Product
 * @package Acme\Product\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class Product implements Pricing\TaxableInterface, Stock\StockSubjectInterface, Subject\SubjectInterface
{
    use Pricing\TaxableTrait,
        Stock\StockSubjectTrait;

    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $designation;

    /**
     * @var string
     */
    private $reference;

    /**
     * @var float
     */
    private $netPrice;

    /**
     * @var float
     */
    private $weight;


    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->initializeStock();
    }

    /**
     * Returns the string representation.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->getDesignation();
    }

    /**
     * Returns the id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @inheritDoc
     */
    public function getIdentifier()
    {
        return $this->getId();
    }

    /**
     * Returns the designation.
     *
     * @return string
     */
    public function getDesignation()
    {
        return $this->designation;
    }

    /**
     * Sets the designation.
     *
     * @param string $designation
     *
     * @return Product
     */
    public function setDesignation($designation)
    {
        $this->designation = $designation;

        return $this;
    }

    /**
     * Returns the reference.
     *
     * @return string
     */
    public function getReference()
    {
        return $this->reference;
    }

    /**
     * Sets the reference.
     *
     * @param string $reference
     *
     * @return Product
     */
    public function setReference($reference)
    {
        $this->reference = $reference;

        return $this;
    }

    /**
     * Returns the netPrice.
     *
     * @return float
     */
    public function getNetPrice()
    {
        return $this->netPrice;
    }

    /**
     * Sets the netPrice.
     *
     * @param float $netPrice
     *
     * @return Product
     */
    public function setNetPrice($netPrice)
    {
        $this->netPrice = $netPrice;

        return $this;
    }

    /**
     * Returns the weight.
     *
     * @return float
     */
    public function getWeight()
    {
        return $this->weight;
    }

    /**
     * Sets the weight.
     *
     * @param float $weight
     *
     * @return Product
     */
    public function setWeight($weight)
    {
        $this->weight = $weight;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getHeight()
    {
        return 0;
    }

    /**
     * @inheritDoc
     */
    public function getWidth()
    {
        return 0;
    }

    /**
     * @inheritDoc
     */
    public function getDepth()
    {
        return 0;
    }

    /**
     * @inheritDoc
     */
    public function getUnit()
    {
        return Units::PIECE;
    }

    /**
     * @inheritDoc
     */
    public function hasDimensions()
    {
        return !empty($this->width) && !empty($this->height) && !empty($this->depth);
    }

    /**
     * @inheritDoc
     */
    public static function getStockUnitClass()
    {
        return StockUnit::class;
    }

    /**
     * @inheritDoc
     */
    static public function getProviderName()
    {
        return ProductProvider::NAME;
    }
}
