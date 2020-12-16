<?php

namespace Acme\Product\Entity;

use Acme\Product\Provider\ProductProvider;
use Ekyna\Component\Commerce\Stock\Model as Stock;

/**
 * Class Product
 * @package Acme\Product\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class Product implements Stock\StockSubjectInterface
{
    use Stock\StockSubjectTrait;

    /**
     * @var int
     */
    private $id;

    /**
     * @var bool
     */
    private $stockCompound = false;

    /**
     * @var Stock\StockComponent[]
     */
    private $stockComposition = [];


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
    public function __toString(): string
    {
        return $this->designation ?: 'New designation';
    }

    /**
     * Returns the id.
     *
     * @return int
     */
    public function getId(): ?int
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
     * Returns the stock composition.
     *
     * @return Stock\StockComponent[]
     */
    public function getStockComposition(): array
    {
        return $this->stockComposition;
    }

    /**
     * Sets the stock composition.
     *
     * @param Stock\StockComponent[]|Stock\StockComponent[][] $components
     *
     * @return Product
     */
    public function setStockComposition(array $components): Product
    {
        $this->stockComposition = $components;

        return $this;
    }

    /**
     * Returns whether the stock is compound.
     *
     * @return bool
     */
    public function isStockCompound(): bool
    {
        return $this->stockCompound;
    }

    /**
     * Sets whether the stock is compound.
     *
     * @param bool $compound
     *
     * @return Product
     */
    public function setStockCompound(bool $compound): Product
    {
        $this->stockCompound = $compound;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public static function getStockUnitClass(): string
    {
        return StockUnit::class;
    }

    /**
     * @inheritDoc
     */
    static public function getProviderName(): string
    {
        return ProductProvider::NAME;
    }
}
